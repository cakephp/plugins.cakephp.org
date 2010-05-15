<?php
class AppModel extends Model {
	var $actsAs = array('Callbackable', 'Containable', 'Lookupable');
	var $recursive = -1;
	var $behaviorData = null;
	var $query = null;

/**
 * Custom find types, as per Matt Curry's method
 *
 * @param   string $type
 * @param   array $options
 * @return  mixed array|integer|boolean
 * @access  public
 * @author  Matt Curry
 * @link    http://github.com/mcurry/find
 */
	function find($type, $options = null) {
		$method = null;
		$options = (array) $options;
		if(is_string($type)) {
			$method = sprintf('__find%s', Inflector::camelize($type));
		}
		if($method && method_exists($this, $method)) {
			$return = $this->{$method}($options);
			if ($this->query != null) {
				unset($this->query['paginate']);
				$query = $this->query;
				$this->query = null;
				return $query;
			}
			return $return;
		}
		if (!empty($options['cache'])) {
			App::import('Vendor', 'mi_cache');
			if (is_int($options['cache'])) MiCache::config(array('duration' => $options['cache']));
			unset($options['cache']);
			return MiCache::data($this->alias, 'find', $type, $options);
		}
		if (!in_array($type, array_keys($this->_findMethods))) {
			diebug(array($type, $options));
			$calledFrom = debug_backtrace();
			CakeLog::write('error', "Unknown method {$this->alias}->{$method} in " . substr(str_replace(ROOT, '', $calledFrom[0]['file']), 1) . ' on line ' . $calledFrom[0]['line'] );
			return false;
		}
		$args = func_get_args();
		return call_user_func_array(array('parent', 'find'), $args);
	}

/**
 * Allows the returning of query parameters for use in pagination
 *
 * @param   array $query
 * @return  boolean
 * @access  public
 * @author  Matt Curry
 */
	function beforeFind($query = array()) {
		$query = (array) $query;
		if (!empty($query['paginate'])) {
			$keys = array('fields', 'order', 'limit', 'page');
			foreach ($keys as $key) {
				if (empty($query[$key]) || (!empty($query[$key]) && empty($query[$key][0]) === null)) {
					unset($query[$key]);
				}
			}
			$this->query = $query;
			return false;
		}
		$this->query = null;
		return true;
	}

/**
 * undocumented function
 *
 * @param   array $data Data to save.
 * @param   mixed $validate Either a boolean, or an array.
 *   If a boolean, indicates whether or not to validate before saving.
 *   If an array, allows control of validate, callbacks, and fieldList
 * @param   array $fieldList List of fields to allow to be written
 * @param   array $extra controls access to optional data a Behavior may want
 * @return  mixed On success Model::$data if its not empty or true, false on failure
 * @access  public
 * @author  Jose Diaz-Gonzalez
 **/
	function save($data = null, $validate = true, $fieldList = array(), $extra = array()) {
		$this->data = (!$data) ? $this->data : $data;
		if (!$this->data) return false;

		$options = array('validate' => true, 'fieldList' => array(), 'callbacks' => true);
		if (is_array($validate)) {
			$options = array_merge($options, $validate);
			foreach($options as $key => &$value) {
				if (!in_array($key, array('validate', 'fieldList', 'callbacks'))) {
					$extra[$key] = $value;
				}
			}
		} else {
			$options = array_merge($options, compact('validate', 'fieldList', 'callbacks'));
		}

		$this->behaviorData = $extra;

		$method = null;
		if (isset($extra['callback']) and is_string($extra['callback'])) {
			$method = sprintf('__beforeSave%s', Inflector::camelize($extra['callback']));
		}

		if($method && method_exists($this, $method)) {
			$this->data = $this->{$method}($this->data, $extra);
		}
		if (!$this->data) return false;
		return parent::save($this->data, $options);
	}

/**
 * Unsets contain key for faster pagination counts
 *
 * @param   array $conditions
 * @param   integer $recursive
 * @param   array $extra
 * @return  integer
 * @access  public
 * @author  Jose Diaz-Gonzalez
 */
	function paginateCount($conditions = null, $recursive = 0, $extra = array()) {
		$extra = (array) $extra;
		$conditions = compact('conditions');
		if ($recursive != $this->recursive) {
			$conditions['recursive'] = $recursive;
		}
		$extra['contain'] = false;
		return $this->find('count', array_merge($conditions, $extra));
	}

/**
 * Updates a particular record without invoking model callbacks
 *
 * @return  boolean True on success, false on Model::id is not set or failure
 * @access  public
 * @author  Jose Diaz-Gonzalez
 **/
	function update($fields, $conditions = array()) {
		$conditions = (array) $conditions;
		if (!$this->id) return false;

		$conditions = array_merge(array("{$this->alias}.$this->primaryKey" => $this->id), $conditions);

		return $this->updateAll($fields, $conditions);
	}

/**
 * Disables/detaches all behaviors from model
 *
 * @param   mixed $except string or array of behaviors to exclude from detachment
 * @param   boolean $detach If true, detaches the behavior instead of disabling it
 * @return  void
 * @access  public
 * @author  Jose Diaz-Gonzalez
 */
	function detachAllBehaviors($except = array(), $detach = false) {
		$except = (array) $except;
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
 * @return  void
 * @access  public
 * @author  Jose Diaz-Gonzalez
 */
	function enableAllBehaviors() {
		$behaviors = $this->Behaviors->attached();
		foreach($behaviors as &$behavior) {
			if (!$this->Behaviors->enabled($behavior)) {
				$this->Behaviors->enable($behavior);
			}
		}
	}

	function __findDistinct($fields = array()) {
		$fields = (array) $fields;

		foreach ($fields as &$field) {
			$field = "DISTINCT {$field}";
		}

		return $this->find('all', array(
			'contain' => false,
			'fields' => $fields));
	}
}
?>