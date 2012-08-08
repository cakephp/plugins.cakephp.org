<?php
/**
 * Methods to display or download any type of file
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs.view
 * @since         CakePHP(tm) v 1.2.0.5714
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::import('View', 'View', false);

class AjaxView extends View {

/**
 * Holds headers sent to browser before rendering media
 *
 * @var array
 * @access protected
 */
	protected $_headers = array();

/**
 * Blacklisted view variables
 *
 * @var string
 */
	protected $_blacklistVars = array(
		'debugToolbarPanels',
		'debugToolbarJavascript',
		'webserviceTextarea',
		'webserviceNoxjson',
		'_message',
		'_redirect',
		'_status',
	);

/**
 * Constructor
 *
 * @param object $controller
 */
	public function __construct(Controller $controller = null) {
		if (is_object($controller)) {
			if (isset($controller->_blacklistVars)) {
				if ($controller->_blacklistVars === false) {
					$this->_blacklistVars = false;
				} else {
					$this->_blacklistVars = array_merge(
						$this->_blacklistVars,
						(array) $controller->_blacklistVars
					);
				}
			}

		}

		parent::__construct($controller);
	}

/**
 * Returns a json-encoded version of certain view variables
 *
 * @return unknown
 */
	public function render($view = null, $layout = null) {
		Configure::write('debug', 0);
		header("Pragma: no-cache");

		$this->_header(array(
			'Expires: Mon, 26 Jul 1997 05:00:00 GMT',
			'Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT',
			'Cache-Control: no-store, no-cache, must-revalidate',
		));
		$this->_header("Cache-Control: post-check=0, pre-check=0", false);

		$this->_header(array(
			'Pragma: no-cache',
			'Content-type: application/json; charset=' . Configure::read('App.encoding'),
		));
		$this->_output();

		foreach ($this->validationErrors as $modelName => $validationError) {
			if (count($validationError) === 0) {
				unset($this->validationErrors[$modelName]);
			}
		}

		if (!isset($this->viewVars['_status'])) {
			$this->viewVars['_status'] = null;
		}

		if (!isset($this->viewVars['_message'])) {
			$this->viewVars['_message'] = null;
		}

		if (in_array($this->viewVars['_status'], array(200, 'success'))) {
			$this->data = array();
		}

		$content = array(
			'validationErrors' => $this->validationErrors,
			'data'             => $this->data,
			'message'          => $this->viewVars['_message'],
			'status'           => $this->viewVars['_status'],
			'content'          => array(),
		);

		if ($this->_blacklistVars) {
			foreach ($this->viewVars as $viewVar => $data) {
				if (in_array($viewVar, $this->_blacklistVars)) {
					continue;
				}

				$content['content'][$viewVar] = $data;
			}
		}

		if (!empty($this->viewVars['_redirect'])) {
			$content['redirect'] = $this->viewVars['_redirect'];
		}
		echo json_encode($content);
		return;
	}

/**
 * Method to set headers
 * @param mixed $header
 * @param boolean $boolean
 * @access protected
 */
	protected function _header($header, $boolean = true) {
		if (is_array($header)) {
			foreach ($header as $string => $boolean) {
				if (is_numeric($string)) {
					$this->_headers[] = array($boolean => true);
				} else {
					$this->_headers[] = array($string => $boolean);
				}
			}
			return;
		}
		$this->_headers[] = array($header => $boolean);
		return;
	}

/**
 * Method to output headers
 * @access protected
 */
	public function _output() {
		foreach ($this->_headers as $key => $value) {
			$header = key($value);
			header($header, $value[$header]);
		}
	}

}