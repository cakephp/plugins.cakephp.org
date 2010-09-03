<?php
/**
 * Clearance Helper class file.
 *
 * Simplifies the construction of HTML elements cleared by permissions in app/config/permit.php.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       sanction
 * @subpackage    sanction.view.helpers
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * Clearance Helper class for easy use of HTML links governed by the app/config/permit.php.
 *
 * ClearanceHelper encloses simply the HtmlHelper::link()
 *
 * @package       sanction
 * @subpackage    sanction.view.helpers
 */
class ClearanceHelper extends AppHelper {
/**
 * Helper dependencies
 *
 * @var array
 * @access public
 */
	var $helpers = array('Html', 'Session');

/**
 * Array of routes connected with Permit::access()
 *
 * @var array
 * @access public
 */
	var $routes = array();


/**
 * Holds the options for the ClearanceHelper
 *
 * The values that may be specified are:
 *  - `$options['path']` Path to the User's Session (First 2 keys)
 * @var array
 * @access public
 */
	var $settings = array(
		'path' => 'Auth.User'
	);

/**
 * Sets the $this->helper to the helper configured in the session
 *
 * @return void
 * @access public
 * @author Jose Diaz-Gonzalez
 **/
	function __construct($config) {
		$this->settings = array_merge($config, $this->settings);
	}

/**
 * Creates an HTML link.
 *
 * If $url starts with "http://" this is treated as an external link. Else,
 * it is treated as a path to controller/action and parsed against the routes
 * included in app/config/permit.php. If there is a match and the User's session
 * clears with the rules, it is then sent off to the HtmlHelper::link() method
 *
 * If the $url is empty, $title is used instead.
 *
 * ### Options
 *
 * - `escape` Set to false to disable escaping of title and attributes.
 *
 * @param string $title The content to be wrapped by <a> tags.
 * @param mixed $url Cake-relative URL or array of URL parameters, or external URL (starts with http://)
 * @param array $options Array of HTML attributes.
 * @param string $confirmMessage JavaScript confirmation message.
 * @return string An `<a />` element.
 * @access public
 * @author Jose Diaz-Gonzalez
 */
	function link($title, $url = null, $options = array(), $confirmMessage = false) {
		if (!is_array($url)) return $this->Html->link($title, $url, $options, $confirmMessage);

		if (!isset($url['plugin']) && !empty($url['plugin'])) $url['plugin'] = $this->params['plugin'];
		if (!isset($url['controller']) && empty($url['controller'])) $url['controller'] = $this->params['controller'];
		if (!isset($url['action']) && empty($url['action'])) $url['action'] = $this->params['action'];

		if (empty($this->routes)) {
			$permit =& Permit::getInstance();

			// $permit->clearances should contain an array of all clearances now
			$this->routes = $permit->clearances;
		}
		if (empty($this->routes)) return $this->Html->link($title, $url, $options, $confirmMessage);

		foreach ($this->routes as $route) {
			if ($this->parse($url, $route)) {
				return $this->execute($route, $title, $url, $options, $confirmMessage);
				break;
			}
		}

		return $this->Html->link($title, $url, $options, $confirmMessage);
	}


/**
 * Parses the passed route against a rule
 *
 * @param string $currentRoute route being testing
 * @param string $route route being tested against
 * @return void
 * @access public
 * @author Jose Diaz-Gonzalez
 */
	function parse(&$currentRoute, &$permit) {
		$route = $permit['route'];

		$count = count($route);
		if ($count == 0) return false;

		foreach ($route as $key => $value) {
			if (isset($currentRoute[$key])) {
				$values = (is_array($value)) ?  $value : array($value);
				foreach ($values as $k => $v) {
					if ($currentRoute[$key] == $v) {
						$count--;
					}
				}
			}
		}
		return $count == 0;
	}


/**
 * Executes the route based on it's rules
 *
 * @param string $route route being executed
 * @param string $title The content to be wrapped by <a> tags.
 * @param mixed $url Cake-relative URL or array of URL parameters, or external URL (starts with http://)
 * @param array $options Array of HTML attributes.
 * @param string $confirmMessage JavaScript confirmation message.
 * @return string An `<a />` element.
 * @access public
 * @author Jose Diaz-Gonzalez
 */
	function execute($route, $title, $url = null, $options = array(), $confirmMessage = false) {
		if (empty($route['rules'])) return $this->Html->link($title, $url, $options, $confirmMessage);

		if (isset($route['rules']['deny'])) {
			return ($route['rules']['deny'] == true) ? null : $this->Html->link($title, $url, $options, $confirmMessage);
		}

		if (!isset($route['rules']['auth'])) return $this->Html->link($title, $url, $options, $confirmMessage);

		if (is_bool($route['rules']['auth'])) {
			$is_authed = $this->Session->read("{$this->settings['path']}.group");

			if ($route['rules']['auth'] == true && !$is_authed) {
				return;
			}
			if ($route['rules']['auth'] == false && $is_authed) {
				return;
			}
			return $this->Html->link($title, $url, $options, $confirmMessage);
		}

		$count = count($route['rules']['auth']);
		if ($count == 0) return $this->Html->link($title, $url, $options, $confirmMessage);

		if (($user = $this->Session->read("{$this->settings['path']}")) == false) {
			return;
		}

		foreach ($route['rules']['auth'] as $field => $value) {
			if ($user[$field] == $value) {
				$count--;
			}
		}

		return ($count != 0) ? null : $this->Html->link($title, $url, $options, $confirmMessage);;
	}

}
?>