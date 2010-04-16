<?php
class PermitComponent extends Object {

	var $controller = null;
	var $Session = null;

	var $settings = array(
		'path' => 'User.User'
	);

/**
 * Array of routes connected with PermitComponent::access()
 *
 * @var array
 * @access public
 */
	var $routes = array();

	var $redirect = '/';

	function initialize(&$controller, $config = array()) {
		$self =& PermitComponent::getInstance();
		if (!include(CONFIGS . DS . 'permit.php')) {
			trigger_error("File containing permissions not found.  It should be located at " . APP_PATH . DS . 'config' . DS . "permit.php", E_USER_ERROR);
		}

		$self->controller = $controller;

		$self->settings = array_merge($self->settings, $config);

		foreach ($self->routes as $route) {
			if (PermitComponent::parse($controller->params, $route)) {
				PermitComponent::execute($route);
				break;
			}
		}
	}

	function parse(&$currentRoute, &$permit) {
		$route = $permit['route'];

		$count = count($route);
		if ($count == 0) return false;

		foreach($route as $key => $value) {

			if (isset($currentRoute[$key])) {
				$values = (is_array($value)) ?  $value : array($value);
				foreach ($values as $k => $v) {
					if ($currentRoute[$key] == $v) {
						$count--;
					}
				}
			}
		}
		return ($count == 0);
	}

	function execute($route) {
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
			$is_authed = $self->Session->read("{$self->settings['path']}.group");
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

		if (($user = $self->Session->read("{$self->settings['path']}")) == false) {
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
			$message = $route['message'];
			$element = $route['element'];
			$params = $route['params'];
			$self->Session->write("Message.{$route['key']}", compact('message', 'element', 'params'));
		}
		$self->controller->redirect($route['redirect']);
	}

	function initializeSessionComponent(&$self) {
		if ($self->Session != null) return $self;

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

class Permit extends Object{

	var $redirect = '/';
	var $clearances = array();

	function access($route, $rules = array(), $redirect = array()) {
		$permitComponent =& PermitComponent::getInstance();
		$self =& Permit::getInstance();
		if (empty($rules)) return $permitComponent->routes;

		$redirect = array_merge(array('redirect' => $this->redirect,
									'message' => __('Access denied', true),
									'trace' => false,
									'element' => 'default',
									'params' => array(),
									'key' => 'flash'),
									$redirect);

		$newRoute = array(
			'route' => $route,
			'rules' => $rules,
			'redirect' => $redirect['redirect'],
			'message' => $redirect['message'],
			'element' => $redirect['element'],
			'params' => $redirect['params'],
			'key' => $redirect['key'],
			'trace' => $redirect['trace']
		);

		$permitComponent->routes[] = $newRoute;
		$self->clearances[] = $newRoute;

		return $permitComponent->routes;
	}

/**
 * Gets a reference to the Permit object instance
 *
 * @return Permit Instance of the Permit.
 * @access public
 * @static
 */
	function &getInstance() {
		static $instance = array();

		if (!$instance) {
			$instance[0] =& new Permit();
		}
		return $instance[0];
	}
}
?>