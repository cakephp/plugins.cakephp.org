<?php
class AccessComponent extends Object {
	var $settings = array();

	var $defaults = array(
		'admin_required' => array(),
		'auth_denied' => array(),
		'auth_required' => array(),
		'denied' => array(),
		'callback' => 'initialize');

	function initialize(&$controller, $settings) {
		$this->settings = array_merge($this->defaults, $settings);
		if ($this->settings['callback'] = 'initialize') {
			$this->_isAuthorized($controller);
		}
	}

	function startup(&$controller) {
		if ($this->settings['callback'] = 'startup') {
			$this->_isAuthorized($controller);
		}
	}

	function _isAuthorized(&$controller) {
		$action = strtolower($controller->params['action']);

		$authRequiredActions = array_map('strtolower', $this->settings['auth_required']);
		$authRequired = ($authRequiredActions == array('*') || in_array($action, $authRequiredActions));
		if ($authRequired and Authsome::get('guest')) {
			$controller->Session->setFlash('Please login to access this resource');
			$controller->redirect(array('controller' => 'users', 'action' => 'login'));
		}

		$authDeniedActions = array_map('strtolower', $this->settings['auth_denied']);
		$authDenied = ($authDeniedActions == array('*') || in_array($action, $authDeniedActions));
		if ($authDenied and !Authsome::get('guest')) {
			$controller->Session->setFlash('You are already logged in');
			$controller->redirect(array('controller' => 'users', 'action' => 'dashboard'));
		}

		$adminRequiredActions = array_map('strtolower', $this->settings['admin_required']);
		$adminRequired = ($adminRequiredActions == array('*') || in_array($action, $adminRequiredActions));
		if ($adminRequired and (Authsome::get('group') != 'administrator')) {
			$controller->Session->setFlash('You must be an administrator to access this resource');
			$controller->redirect(array('controller' => 'users', 'action' => 'dashboard'));
		}

		$deniedActions = array_map('strtolower', $this->settings['denied']);
		$denied = ($deniedActions == array('*') || in_array($action, $deniedActions));
		if ($denied) {
			$controller->Session->setFlash('You do not have access to this resource');
			$controller->redirect(array('controller' => 'users', 'action' => 'index'));
		}
	}
}
?>