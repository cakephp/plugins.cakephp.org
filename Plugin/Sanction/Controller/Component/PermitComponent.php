<?php
App::uses('Component', 'Controller');
App::uses('Router', 'Routing');
App::uses('CakeException', 'Error');
App::uses('Set', 'Utility');

/**
 * Exception class for Permit Component.  This exception will be thrown from Permit
 * component when it encounters an error.
 *
 * @package       Sanction.Controller.Component
 */
class PermitException extends CakeException {

}

/**
 * Permit component class
 *
 * Manages user access to a given route
 *
 * @package       Sanction
 * @subpackage    Sanction.Controller.Component
 */
class PermitComponent extends Component {

/**
 * Other components utilized by PermitComponent
 *
 * @param array
 */
	public $components = array('Session');

/**
 * Parameter data from Controller::$params
 *
 * @param array
 */
	public $settings = array(
		'path' => 'Auth.User',
		'check' => 'id',
		'isTest' => false,
	);

/**
 * Array of routes connected with PermitComponent::access()
 *
 * @param array
 */
	public $routes = array();

/**
 * Array containing executed route
 *
 * @param array
 */
	public $executed = null;

/**
 * Maintains current logged in user.
 *
 * @param boolean
 */
	public $user = null;

/**
 * Constructor.
 *
 * @param ComponentCollection $collection
 * @param array $settings
 * @throws PermitException
 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		$this->settings['permit_include_path'] = APP . 'Config' . DS . 'permit.php';
		$settings = array_merge($this->settings, $settings);

		if (!$settings['isTest']) {
			if (!file_exists($settings['permit_include_path'])) {
				throw new PermitException("File containing permissions not found.  It should be located at " . APP . 'Config' . DS . "permit.php");
			}

			try {
				include $settings['permit_include_path'];
			} catch (Exception $e) {
				throw new PermitException("File containing permissions not found.  It should be located at " . APP . 'config' . DS . "permit.php");
			}
		}

		parent::__construct($collection, $settings);
		Permit::$settings = $this->settings;
	}

/**
 * Initializes SanctionComponent for use in the controller
 *
 * @param object $controller A reference to the instantiating controller object
 * @return void
 */
	public function initialize(Controller $controller) {
		if ($this->settings['isTest']) {
			return;
		}

		$this->user = $this->Session->read("{$this->settings['path']}");

		$this->routes = Permit::$routes;
		Permit::$user = $this->user;

		$this->request = $controller->request;

		foreach (array('controller', 'plugin') as $inflected) {
			if (isset($this->request->params[$inflected])) {
				$this->request->params[$inflected] = strtolower(Inflector::underscore($this->request->params[$inflected]));
			}
		}
	}

/**
 * Main execution method.  Handles redirecting of invalid users, and saving
 * of request url as Sanction.referer
 *
 * @param object $controller A reference to the instantiating controller object
 * @return boolean
 */
	public function startup(Controller $controller) {
		if ($this->settings['isTest']) {
			return;
		}

		foreach ($this->routes as $route) {
			if (!$this->_parse($route['route'])) {
				continue;
			}

			if (!$this->_execute($route)) {
				break;
			}

			$this->Session->write('Sanction.referer', $this->request->here());
			return $this->redirect($controller, $route);
		}
	}

	protected function _ensureHere() {
		if (empty($this->_here) || empty($this->_hereQuery)) {
			$this->_here = rtrim(preg_replace(
				'/^' . preg_quote($this->request->base, '/') . '/',
				'',
				$this->request->here,
				1
			), '/');
			$this->_hereQuery = rtrim($this->request->here(false), '/');
		}
	}

/**
 * Parses a given Permit route to see if it matches the current request
 *
 * @param object $controller A reference to the instantiating controller object
 * @param array $route A Permit Route
 * @return boolean true if current request matches Permit route, false otherwise
 */
	protected function _parse($route) {
		if (is_string($route)) {
			$this->_ensureHere();

			$url = parse_url($route);
			$_path = rtrim($url['path'], '/');
			if ($_path . '?' . Hash::get($url, 'query') === $this->_here_query) {
				return true;
			}

			if ($_path === $this->_here) {
				return true;
			}

			return false;
		}

		$count = count($route);
		if ($count == 0) {
			return false;
		}

		foreach ($route as $key => $value) {
			if (array_key_exists($key, $this->request->params)) {
				$values = (array)$value;
				$check = (array)$this->request->params[$key];

				$hasNullValues = (count($values) != count(array_filter($values)) || count($values) == 0);
				$currentValueIsNullish = (in_array(null, $check) || in_array('', $check) || count($check) == 0);
				if ($hasNullValues && $currentValueIsNullish) {
					$count--;
					continue;
				}

				if (in_array($key, array('controller', 'plugin'))) {
					foreach ($check as $k => $_check) {
						$check[$k] = Inflector::underscore(strtolower($_check));
					}
				} else {
					foreach ($check as $k => $_check) {
						$check[$k] = strtolower($_check);
					}
				}

				if (count($values) > 0) {
					foreach ($values as $k => $v) {
						if (in_array(strtolower($v), $check)) {
							$count--;
							break;
						}
					}
				} elseif (count($check) === 0) {
					$count--;
				}
			} elseif (is_numeric($key) && isset($this->request->params['pass'])) {
				if (is_array($this->request->params['pass'])) {
					if (Hash::contains($this->request->params['pass'], $value)) {
						$count--;
					}
				}
			}
		}

		return ($count == 0);
	}

/**
 * Determines whether the given user is authorized to perform an action.  The result of
 * a failed request depends upon the options for the route
 *
 * @param array $route A Permit Route
 * @return boolean True if redirect should be executed, false otherwise
 */
	protected function _execute($route) {
		Permit::$executed = $this->executed = $route;

		if (empty($route['rules'])) {
			return false;
		}

		if (isset($route['rules']['deny'])) {
			return $route['rules']['deny'] == true;
		}

		if (!isset($route['rules']['auth'])) {
			return false;
		}

		if (is_bool($route['rules']['auth'])) {
			$isAuthed = $this->Session->read("{$this->settings['path']}.{$this->settings['check']}");

			if ($route['rules']['auth'] == true && !$isAuthed) {
				return true;
			}

			if ($route['rules']['auth'] == false && $isAuthed) {
				return true;
			}

			return false;
		} elseif (!is_array($route['rules']['auth'])) {
			return false;
		}

		if ($this->user == false) {
			return true;
		}

		$fieldsBehavior = 'and';
		if (!empty($route['rules']['fields_behavior'])) {
			$fieldsBehavior = strtolower($route['rules']['fields_behavior']);
		}

		if (!in_array($fieldsBehavior, array('and', 'or'))) {
			$fieldsBehavior = 'and';
		}

		$count = count(Set::flatten($route['rules']['auth']));
		foreach ($route['rules']['auth'] as $path => $condition) {
			$path = '/' . str_replace('.', '/', $path);
			$path = preg_replace('/^([\/]+)/', '/', $path);

			$check = $condition;
			$continue = false;
			$decrement = 1;
			$values = Set::extract($path, $this->user);

			// Support for OR Model-syntax
			foreach (array('or', 'OR') as $anOr) {
				if (is_array($condition) && array_key_exists($anOr, $condition)) {
					$check = $condition[$anOr];
					$continue = true;
					$decrement = count($check);
				}
			}

			if ($fieldsBehavior == 'or') {
				$check = $condition;
				$continue = true;
				$decrement = count($check);
			}

			foreach ((array)$check as $cond) {
				if (in_array($cond, (array)$values)) {
					$count -= $decrement;
					if ($continue) {
						continue 2;
					}
				}
			}
		}

		return $count !== 0;
	}

/**
 * Performs a redirect based upon a given route
 *
 * @param object $controller A reference to the instantiating controller object
 * @param array $route A Permit Route
 * @return void
 */
	public function redirect(&$controller, $route) {
		if ($route['message'] != null) {
			$message = $route['message'];
			$element = $route['element'];
			$params = $route['params'];
			$this->Session->write("Message.{$route['key']}", compact('message', 'element', 'params'));
		}

		$controller->redirect($route['redirect']);
	}

/**
 * Connects a route to a given ruleset
 *
 * @param array $route array describing a route
 * @param array $rules array of rules regarding the route
 * @param array $redirect Array containing the url to redirect to on route fail
 * @return array Array of connected routes
 */
	public function access($route, $rules = array(), $redirect = array()) {
		$this->routes[] = Permit::access($route, $rules, $redirect);
	}

/**
 * Returns the referring URL for this request.
 *
 * @param mixed $default Default URL to use if Session cannot be read
 * @return string Referring URL
 */
	public function referer($referer = null) {
		if ($this->Session->check('Sanction.referer')) {
			$referer = $this->Session->read('Sanction.referer');
			$this->Session->delete('Sanction.referer');
		}

		if ($referer === null) {
			return false;
		}

		return Router::normalize($referer);
	}

}

/**
 * Permit class
 *
 * Connects routes for a given request
 *
 * @package       Sanction
 * @subpackage    Sanction.Controller.Component
 */
class Permit extends Object {

	public static $executed = null;

	public static $routes = array();

	public static $settings = null;

	public static $user = null;

/**
 * Connects a route to a given ruleset
 *
 * Also converts underscored names to camelCase as
 * additional way of accessing a controller
 *
 * @param array $route array describing a route
 * @param array $rules array of rules regarding the route
 * @param array $redirect Array containing the url to redirect to on route fail
 * @return array Array of connected routes
 */
	public static function access($route, $rules = array(), $redirect = array()) {
		$redirect = array_merge(array(
				'redirect' => '/',
				'message' => __('Access denied', true),
				'element' => 'default',
				'params' => array(),
				'key' => 'flash'
			),
			$redirect
		);

		if (is_array($route)) {
			foreach (array('controller', 'plugin') as $inflected) {
				if (isset($route[$inflected])) {
					if (is_array($route[$inflected])) {
						foreach ($route[$inflected] as $i => $controllerName) {
							$route[$inflected][$i] = Inflector::underscore($controllerName);
						}
					} else {
						$route[$inflected] = Inflector::underscore($route[$inflected]);
					}
				}
			}

			foreach ($route as $k => $value) {
				if (is_array($value)) {
					foreach ($value as $i => $_value) {
						$route[$k][$i] = strtolower($_value);
					}
				} else {
					$route[$k] = strtolower($value);
				}
			}
		}

		$newRoute = array(
			'route' => $route,
			'rules' => $rules,
			'redirect' => $redirect['redirect'],
			'message' => $redirect['message'],
			'element' => $redirect['element'],
			'params' => $redirect['params'],
			'key' => $redirect['key'],
		);

		self::$routes[] = $newRoute;
		return $newRoute;
	}

}
