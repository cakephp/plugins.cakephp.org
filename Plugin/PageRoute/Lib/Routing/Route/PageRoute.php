<?php
App::uses('Sanitize', 'Utility');
App::uses('ConnectionManager', 'Model');

/**
 * Custom Route class auto-enables /:page routes
 * Enables you to add new pages without having to manually specify a shortcut
 * route in your routes.php file
 *
 * To use, drop this into app/libs/routes/page_route.php and add
 * the following to the top of app/config/routes.php:
 *
 * App::import('Lib', 'routes/PageRoute');
 *
 * To trigger it, specify the routeClass in the route's options array, along
 * with the regex to allow subpages to be parsed:
 *
 * Router::connect('/:page', array(),
 *     array('routeClass' => 'PageRoute', 'page' => '[\/\w_-]+')
 * );
 *
 * Note that if a page has the same name as a controller/plugin, the page will
 * take precedence since it is included before Router::__connectDefaultRoutes()
 * is called.
 *
 * @author Jose Gonzalez (support@savant.be)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @see CakeRoute
 */
class PageRoute extends CakeRoute {

/**
 * An array of additional parameters for the Route.
 *
 * @var array
 * @access public
 */
	var $options = array(
		'controller' => 'pages',
		'action' => 'display',
		'page' => '[\/\w_-]+',
	);

/**
 * Constructor for a Route
 *
 * @param string $template Template string with parameter placeholders
 * @param array $defaults Array of defaults for the route.
 * @param string $params Array of parameters and additional options for the Route
 * @return void
 */
	public function __construct($template, $defaults = array(), $options = array()) {
		$options = array_merge($this->options, (array) $options);
		parent::__construct($template, $defaults, $options);
	}

/**
 * Parses a string url into an array.  If a page is found, it is parsed into
 * the pass key in the route params
 *
 * @param string $url The url to parse
 * @return mixed false on failure, or an array of request parameters
 */
	function parse($url) {
		$params = parent::parse($url);
		if (!$params || empty($params['page'])) {
			return false;
		}

		$path = trim(str_replace('//', '', (string) $params['page']), '/');
		if (!file_exists(APP . 'View' . DS . $this->options['controller'] . DS . $path . '.ctp')) {
			return false;
		}

		$params['pass'] = Sanitize::clean(explode('/', $path));
		$params['controller'] = $this->options['controller'];
		$params['action'] = $this->options['action'];
		$params['plugin'] = null;
		unset($params['page']);

		return $params;
	}

/**
 * Reverse route page shortcut urls. Treats all existing page routes as normal
 * Can optionally validate a page path using the 'validate' key
 *
 * @param array $url Array of parameters to convert to a string.
 * @return mixed either false or a string url.
 */
	function match($url) {
		if (!isset($url['controller']) || !isset($url['action'])) {
			return false;
		}

		if ($url['controller'] != $this->options['controller'] || $url['action'] != $this->options['action']) {
			return false;
		}

		if (isset($url[0]) && !isset($url['page'])) {
			$url['page'] = $url[0];
		}

		if (!isset($url['page'])) {
			return false;
		}

		if (isset($url['validate']) && $url['validate'] == true) {
			$path = trim(str_replace('//', '', (string) $url['page']), '/');
			if (!file_exists(APP . 'View' . DS . $this->options['controller'] . DS . $path . '.ctp')) {
				return false;
			}
		}

		unset($url[0], $url['prefix'], $url['plugin'], $url['validate']);
		return parent::match($url);
	}

}