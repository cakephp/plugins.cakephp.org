<?php
class PermitComponent extends Object {

	var $sessionString = 'Maintainer.Maintainer';
	var $controller = null;

/**
 * Array of routes connected with PermitComponent::access()
 *
 * @var array
 * @access public
 */
	var $routes = array();

	var $redirect = '/';

	function initialize(&$controller) {
		$self =& PermitComponent::getInstance();
		if (!include(CONFIGS . DS . 'permit.php')) {
			trigger_error("File containing permissions not found.  It should be located at " . APP_PATH . DS . 'config' . DS . "permit.php", E_USER_ERROR);
		}

		$self->controller = $controller;

		foreach ($self->routes as $route) {
			if (PermitComponent::parse($controller->params, $route)) {
				PermitComponent::execute($route);
				break;
			}
		}
	}

	function access($route, $rules = array(), $redirect = array()) {
		$self =& PermitComponent::getInstance();
		if (empty($rules)) return $self->routes;

		$redirect = array_merge(array('redirect' => $this->redirect,
									'message' => __('Access denied', true),
									'trace' => false,
									'element' => 'default',
									'params' => array(),
									'key' => 'flash'),
									$redirect);

		$self->routes[] = array(
			'route' => $route,
			'rules' => $rules,
			'redirect' => $redirect['redirect'],
			'message' => $redirect['message'],
			'element' => $redirect['element'],
			'params' => $redirect['params'],
			'key' => $redirect['key'],
			'trace' => $redirect['trace']
		);

		return $self->routes;
	}

	function parse(&$currentRoute, &$permit) {
		$self =& PermitComponent::getInstance();
		$route = $permit['route'];

		$count = count($route);
		if ($count == 0) return false;
		if ($permit['trace'] && Configure::read()) debug($count);

		foreach($route as $key => $value) {
			if ($permit['trace'] && Configure::read()) debug($key);

			if (isset($currentRoute[$key])) {
				if ($permit['trace'] && Configure::read()) debug($key);
				$values = (is_array($value)) ?  $value : array($value);
				foreach ($values as $k => $v) {
					if ($currentRoute[$key] == $v) {
						if ($permit['trace'] && Configure::read()) debug($v);
						$count--;
					}
				}
			}
		}
		if ($permit['trace'] && Configure::read()) debug($count);
		return ($count == 0);
	}

	function execute($route) {
		if ($route['trace'] && Configure::read()) debug($route);
		$self =& PermitComponent::getInstance();
		$self = $self->initializeSessionComponent($self);

		if (empty($route['rules'])) return;

		if (isset($route['rules']['deny'])) {
			if ($route['rules']['deny'] == true) {
				$self->redirect($route);
			}
			return;
		}

		if (!isset($route['rules']['auth'])) return;

		if (is_bool($route['rules']['auth'])) {
			$is_authed = $self->Session->read("{$self->sessionString}.group");

			if ($route['rules']['auth'] == true && !$is_authed) {
				$self->redirect($route);
			}
			if ($route['rules']['auth'] == false && $is_authed) {
				$self->redirect($route);
			}
			return;
		}

		$count = count($route['rules']['auth']);
		if ($count == 0) return;

		if (($user = $self->Session->read("{$self->sessionString}")) == false) {
			$self->redirect($route);
		}

		foreach ($route['rules']['auth'] as $field => $value) {
			if ($user[$field] == $value) {
				$count--;
			}
		}

		if ($count != 0) {
			$self->redirect($route);
		}
	}

	function redirect($route) {
		$self =& PermitComponent::getInstance();

		if ($route['message'] != null) {
			$session = new CakeSession();
			$message = $route['message'];
			$element = $route['element'];
			$params = $route['params'];
			$session->write("Message.{$route['key']}", compact('message', 'element', 'params'));
		}
		$self->controller->redirect($route['redirect']);
	}

	function initializeSessionComponent(&$self) {
		App::import('Component', 'Session');
		$componentClass = 'SessionComponent';
		$self->Session =& new $componentClass(null);

		if (method_exists($self->Session, 'initialize')) {
			$self->Session->initialize($self->controller);
		}

		if (method_exists($self->Session, 'startup')) {
            $self->Session->startup($self->controller);
		}

		return $self;
	}

/**
 * Gets a reference to the PermitComponent object instance
 *
 * @return PermitComponent Instance of the PermitComponent.
 * @access public
 * @static
 */
	function &getInstance() {
		static $instance = array();

		if (!$instance) {
			$instance[0] =& new PermitComponent();
		}
		return $instance[0];
	}

}
?>