<?php
/**
 * DataTable Config
 *
 * @package Plugin.DataTable
 * @subpackage Plugin.DataTable.Controller.Component.DataTable
 * @author Tigran Gabrielyan
 */
class DataTableConfig {

	const SEARCH_GLOBAL = 1;

	const SEARCH_COLUMN = 2;

	/**
	 * @var array
	 */
	public $defaults = array(
		'autoData' => true,
		'autoRender' => true,
		'columns' => array(),
		'conditions' => array(),
		'maxLimit' => 100,
		'viewVar' => 'dtResults',
		'order' => array(),
	);

	/**
	 * @var string
	 */
	protected $_name;

	/**
	 * @var array
	 */
	protected $_config;

	/**
	 *
	 * @param string $name Name of the config to parse
	 * @param array $config List of configs
	 */
	public function __construct($name, $config) {
		if (!isset($config[$name])) {
			throw new Exception(sprintf('%s: Missing config %s', __CLASS__, $name));
		}

		$this->_name = $name;
		$this->_config = array_merge($this->defaults, $config, $config[$name]);
		$this->_parse();
	}

	/**
	 * Returns reference to config values
	 *
	 * @return mixed
	 */
	public function &__get($name) {
		return $this->_config[$name];
	}

	/**
	 * Sets config values
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value) {
		$this->_config[$name] = $value;
	}

	/**
	 * Gets query array
	 *
	 * @return array
	 */
	public function getQuery() {
		return array_diff_key($this->_config, array_flip([
			'columns', 'viewVar', $this->_name
		]));
	}

	/**
	 * Gets count query array
	 *
	 * @return array
	 */
	public function getCountQuery() {
		return array_diff_key($this->_config, array_flip([
			'columns', 'viewVar', 'fields', 'limit', 'offset', $this->_name
		]));
	}

	/**
	 * Converts field to Model.field
	 *
	 * @param string $object
	 * @param string $field
	 * @return string
	 */
	protected function _toColumn($alias, $field) {
		return (strpos($field, '.') !== false) ? $field : $alias . '.' . $field;
	}

	/**
	 * Parse the config specified
	 *
	 * @return void
	 */
	protected function _parse() {
		if (!$this->model) {
			$this->model = $this->_name;
		}

		if (!$this->view) {
			$this->view = $this->_name;
		}

		if ($this->scope) {
			$this->conditions[] = $this->scope;
		}

		$columns = array();
		foreach ($this->columns as $field => $options) {
			$useField = !is_null($options);
			$enabled = $useField && (!isset($options['useField']) || $options['useField']);
			if (is_numeric($field)) {
				$field = $options;
				$options = array();
			}
			if (is_bool($options)) {
				$enabled = $options;
				$options = array();
			}
			$label = Inflector::humanize($field);
			if (is_string($options)) {
				$label = $options;
				$options = array();
			}
			$defaults = array(
				'useField' => $useField,
				'label' => $label,
				'bSortable' => $enabled,
				'bSearchable' => $enabled,
			);
			$options = array_merge($defaults, (array) $options);
			$column = ($options['useField']) ? $this->_toColumn($this->model, $field) : $field;
			$columns[$column] = $options;
			if ($options['useField']) {
				$this->fields[] = $column;
			}
		}
		$this->columns = $columns;
	}
}