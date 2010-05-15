<?php
class ClearanceHelper extends AppHelper {

	var $helpers = array('Html', 'Session');
	var $routes = array();
	var $sessionString = 'Maintainer.Maintainer';

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

	function execute($route, $title, $url = null, $options = array(), $confirmMessage = false) {
		if (empty($route['rules'])) return $this->Html->link($title, $url, $options, $confirmMessage);

		if (isset($route['rules']['deny'])) {
			return ($route['rules']['deny'] == true) ? null : $this->Html->link($title, $url, $options, $confirmMessage);
		}

		if (!isset($route['rules']['auth'])) return $this->Html->link($title, $url, $options, $confirmMessage);

		if (is_bool($route['rules']['auth'])) {
			$is_authed = $this->Session->read("{$this->sessionString}.group");

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

		if (($user = $this->Session->read("{$this->sessionString}")) == false) {
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