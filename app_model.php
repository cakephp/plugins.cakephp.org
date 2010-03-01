<?php
class AppModel extends Model {
	var $actsAs = array('Containable', 'Lookupable');
	var $recursive = -1;

/**
 * Custom find types, as per Matt Curry's method
 *
 * @param string $type 
 * @param array $options 
 * @return mixed array|integer|boolean
 * @author Matt Curry
 * @link http://github.com/mcurry/find
 */
	function find($type, $options = array()) {
		$method = null;
		if(is_string($type)) {
			$method = sprintf('__find%s', Inflector::camelize($type));
		}
		if($method && method_exists($this, $method)) {
			$return = $this->{$method}($options);
			if($return === null && !empty($this->query['paginate'])) {
				unset($this->query['paginate']);
				$query = $this->query;
				$this->query = null;
				return $query;
			}
			return $return;
		} else if (!empty($options['cache'])) {
			App::import('Vendor', 'mi_cache');
			unset($options['cache']);
			return MiCache::data($this->alias, 'find', $type, $options);
		} else {
			$args = func_get_args();
			return call_user_func_array(array('parent', 'find'), $args);
		}
	}

/**
 * Disables/detabches all behaviors from model
 *
 * @param mixed $except string or array of behaviors to exclude from detachment
 * @param boolean $detach If true, detaches the behavior instead of disabling it
 * @return void
 * @author Jose Diaz-Gonzalez
 */
	function detachAllBehaviors($except = null, $detach = false) {
		if ($except and !is_array($except)) {
			$except = array($except);
		}
		$behaviors = $this->Behaviors->attached();
		foreach ($behaviors as &$behavior) {
			if (!in_array($behavior, $except)) {
				if (!$detach) {
					$this->Behaviors->disable($behavior);
				} else {
					$this->Behaviors->detach($behavior);
				}
			}
		}
	}

/**
 * Enables all previously disabled attachments
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 */
	function enableAllBehaviors() {
		$behaviors = $this->Behaviors->attached();
		foreach($behaviors as &$behavior) {
			if (!$this->Behaviors->enabled($behavior)) {
				$this->Behaviors->enable($behavior);
			}
		}
	}
}
?>