<?php
App::uses('HtmlHelper', 'View/Helper');
/**
 * DataTable Helper
 *
 * @package Plugin.DataTable
 * @subpackage Plugin.DataTable.View.Helper
 * @author Tigran Gabrielyan
 *
 * @property HtmlHelper $Html
 */
class DataTableHelper extends HtmlHelper {

	/**
	 * Settings
	 *
	 * - `table` See `render()` method for setting info
	 * - `scriptBlock` String for block name or false to disable output of default init script
	 * - `js` See `script()` method for setting info
	 *
	 * @var array
	 */
	public $settings = array(
		'table' => array(
			'class' => 'dataTable',
			'trOptions' => array(),
			'thOptions' => array(),
			'theadOptions' => array(),
			'tbody' => '',
			'tbodyOptions' => array(),
			'tfoot' => '',
			'tfootOptions' => array(),
		),
		'scriptBlock' => 'script',
		'js' => array(
			'aoColumns' => true,
			'sAjaxSource' => array('action' => 'processDataTableRequest'),
			'bServerSide' => true,
		),
	);

	/**
	 * Table header labels
	 *
	 * @var array
	 */
	protected $_labels = array();

	/**
	 * Column data passed from controller
	 *
	 * @var array
	 */
	protected $_dtColumns;

	/**
	 * Javascript settings for all pagination configs
	 *
	 * @var
	 */
	protected $_dtSettings = array();

	/**
	 * Constructor
	 *
	 * @param View $View The View this helper is being attached to.
	 * @param array $settings Configuration settings for the helper.
	 */
	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
		if (isset($this->_View->viewVars['dtColumns'])) {
			foreach ($this->_View->viewVars['dtColumns'] as $config => $columns) {
				$this->_parseSettings($config, $columns);
			}
		}
	}

	/**
	 * Output dataTable settings to script block
	 *
	 * @param string $viewFile
	 */
	public function afterRender($viewFile) {
		$jsVar = sprintf('var dataTableSettings = %s;', json_encode($this->_dtSettings));
		$this->scriptBlock($jsVar, array('block' => 'dataTableSettings'));
		if ($this->settings['scriptBlock'] !== false) {
			$initScript = <<< INIT_SCRIPT
$(document).ready(function() {
	$('.dataTable').each(function() {
		var table = $(this);
		var settings = dataTableSettings[table.attr('data-config')];
		table.dataTable(settings);
	});
});
INIT_SCRIPT;
			$this->scriptBlock($initScript, array('block' => $this->settings['scriptBlock']));
		}
	}

	/**
	 * Renders a DataTable
	 *
	 * Options take on the following values:
	 * - `class` For table. Default: `dataTable`
	 * - `trOptions` Array of options for tr
	 * - `thOptions` Array of options for th
	 * - `theadOptions` Array of options for thead
	 * - `tbody` Content for tbody
	 * - `tbodyOptions` Array of options for tbody
	 *
	 * The rest of the keys wil be passed as options for the table
	 *
	 * @param string $config Config to render
	 * @param array $options Options for table
	 * @param array $js Options for js var
	 * @return string
	 */
	public function render($config = null, $options = array(), $js = array()) {
		if ($config === null) {
			$config = current(array_keys($this->request->params['models']));
		}

		$options = array_merge($this->settings['table'], $options);

		$trOptions = $options['trOptions'];
		$thOptions = $options['thOptions'];
		unset($options['trOptions'], $options['thOptions']);

		$theadOptions = $options['theadOptions'];
		$tbodyOptions = $options['tbodyOptions'];
		$tfootOptions = $options['tfootOptions'];
		unset($options['theadOptions'], $options['tbodyOptions'], $options['tfootOptions']);

		$tbody = $options['tbody'];
		$tfoot = $options['tfoot'];
		unset($options['tbody'], $options['tfoot']);

		$tableHeaders = $this->tableHeaders($this->_labels[$config], $trOptions, $thOptions);
		$tableHead = $this->tag('thead', $tableHeaders, $theadOptions);
		$tableBody = $this->tag('tbody', $tbody, $tbodyOptions);
		$tableFooter = $this->tag('tfoot', $tfoot, $tfootOptions);
		$options['data-config'] = $config;
		$table = $this->tag('table', $tableHead . $tableBody . $tableFooter, $options);
		$this->jsSettings($config, $js);

		return $table;
	}

	/**
	 * Renders table headers with column-specific attribute support
	 *
	 * @param $names
	 * @param null $trOptions
	 * @param null $thOptions
	 * @return string
	 */
	public function tableHeaders($names, $trOptions = null, $thOptions = null) {
		$out = array();
		foreach ($names as $name) {
			$arg = $name;
			$options = array();
			if (is_array($name)) {
				list($arg, $options) = $name;
			}
			$options = array_merge((array)$thOptions, $options);
			$out[] = sprintf($this->_tags['tableheader'], $this->_parseAttributes($options), $arg);
		}
		return sprintf($this->_tags['tablerow'], $this->_parseAttributes($trOptions), join(' ', $out));
	}

	/**
	 * Sets label at the given index.
	 *
	 * @param string $config
	 * @param int $index of column to change
	 * @param string $label new label to be set. `__LABEL__` string will be replaced by the original label
	 */
	public function setLabel($config, $index, $label) {
		$oldLabel = $this->_labels[$config][$index];
		$oldOptions = $options = array();
		if (is_array($oldLabel)) {
			list($oldLabel, $oldOptions) = $oldLabel;
		}
		if (is_array($label)) {
			list($label, $options) = $label;
		}
		$this->_labels[$config][$index] = array(
			$this->_parseLabel($label, $oldLabel),
			array_merge($oldOptions, $options),
		);
	}

	/**
	 * Returns js settings either as an array or json-encoded string
	 *
	 * @param array $settings
	 * @param bool $encode
	 * @return array|string
	 */
	public function jsSettings($config, $settings = array(), $encode = false) {
		$settings = array_merge($this->settings['js'], (array)$settings);
		if (!empty($settings['bServerSide'])) {
			if (!isset($settings['sAjaxSource']) || $settings['sAjaxSource'] === true) {
				$settings['sAjaxSource'] = $this->request->here();
			}
			if (!is_string($settings['sAjaxSource'])) {
				$settings['sAjaxSource']['?']['config'] = $config;
				$settings['sAjaxSource'] = Router::url($settings['sAjaxSource']);
			}
		}
		if (isset($settings['aoColumns']) && $settings['aoColumns'] === true) {
			$settings['aoColumns'] = $this->_dtColumns[$config];
		}
		$this->_dtSettings[$config] = $settings;
		return ($encode) ? json_encode($settings) : $settings;
	}

	/**
	 * Parse a label with its options
	 *
	 * @param $label
	 * @param string $oldLabel
	 * @return string
	 */
	protected function _parseLabel($label, $oldLabel = '') {
		$replacements = array(
			'__CHECKBOX__' => '<input type="checkbox" class="check-all">',
			'__LABEL__' => $oldLabel,
		);
		foreach ($replacements as $search => $replace) {
			$label = str_replace($search, $replace, $label);
		}
		return $label;
	}

	/**
	 * Parse settings
	 *
	 * @param string $config
	 * @param array $columns
	 * @return array
	 */
	protected function _parseSettings($config, $columns) {
		foreach ($columns as $field => $options) {
			if ($options === null) {
				$label = $field;
				$options = array(
					'bSearchable' => false,
					'bSortable' => false,
				);
			} else {
				$label = $options['label'];
				unset($options['label']);
				if (isset($options['bSearchable'])) {
					$options['bSearchable'] = (boolean)$options['bSearchable'];
				}
			}
			$this->_labels[$config][] = $this->_parseLabel($label);
			$this->_dtColumns[$config][] = $options;
		}
		return $this->_dtColumns[$config];
	}
}