<?php
/**
 * DataTableResponse View
 *
 * @package Plugin.DataTable
 * @subpackage Plugin.DataTable.View
 * @author Tigran Gabrielyan
 */
App::uses('JsonView', 'View');
class DataTableResponseView extends JsonView {

	/**
	 * DataTable views are always located in the 'datatable' sub directory for a
	 * controllers views.
	 *
	 * @var string
	 */
	public $subDir = 'datatable';

	/**
	 * Response data for DataTable
	 *
	 * @var array
	 */
	public $dtResponse;

	/**
	 * Constructor
	 *
	 * @param Controller $controller
	 */
	public function __construct(Controller $controller = null) {
		parent::__construct($controller);
		$this->dtResponse = $this->viewVars['dataTableData'];
	}

	/**
	 * Renders file and returns json-encoded response
	 *
	 * @param null $view
	 * @param null $layout
	 * @return string
	 */
	public function render($view = null, $layout = null) {
		parent::render($view, $layout);
		$options = 0;
		if (version_compare(PHP_VERSION, '5.4.0', '>=') && Configure::read('debug')) {
			$options = JSON_PRETTY_PRINT;
		}
		return json_encode($this->dtResponse, $options);
	}

}