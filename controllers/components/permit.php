<?php
class PermitComponent extends Object {

	var $model = 'Maintainer';

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
			if (PermitComponent::parse($controller->params, $route['route'])) {
				PermitComponent::execute($route);
			}
		}
	}

	function access($route, $rules = array(), $redirect = array()) {
		$self =& PermitComponent::getInstance();
		if (empty($rules)) return $self->routes;

		$redirect = array_merge(array('redirect' => $this->redirect,
									'message' => __('Access denied', true),
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
		);

		return $self->routes;
	}

	function parse($currentRoute, $route) {
		$self =& PermitComponent::getInstance();

		$count = count($route);
		if ($count == 0) return false;

		foreach($route as $key => $value) {
			if (isset($currentRoute[$key])) {
				$values = (is_array($value)) ?  $value : array($value);
				foreach ($values as $k => $v) {
					if ($currentRoute[$key] == $value) {
						$count--;
					}
				}
			}
		}
		return ($count == 0);
	}

	function execute($route) {
		$self =& PermitComponent::getInstance();

		if (empty($route['rules'])) return;

		if (isset($route['rules']['deny'])) {
			if ($route['rules']['deny'] == true) {
				$self->redirect($route);
			}
			return;
		}

		if (!isset($route['rules']['auth'])) return;

		if (is_bool($route['rules']['auth'])) {
			$is_authed = Authsome::get($this->model);

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

		if (($user = Authsome::get($this->model)) == false) {
			$self->redirect($route);
		}

		foreach ($route['rules']['auth'] as $field => $value) {
			if ($user[$field] == $value) {
				$count--;
			}
		}

		if ($count == 0) return;

		$self->redirect($route);
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