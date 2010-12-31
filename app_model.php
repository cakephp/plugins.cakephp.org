<?php
/**
 * Application Model class
 *
 * Add your application-wide methods to the class, your models will inherit them.
 *
 * @package       app
 */
class AppModel extends Model {
/**
 * List of behaviors to load when the model object is initialized. Settings can be
 * passed to behaviors by using the behavior name as index. Eg:
 *
 * var $actsAs = array('Translate', 'MyBehavior' => array('setting1' => 'value1'))
 *
 * @var array
 * @access public
 * @link http://book.cakephp.org/view/1072/Using-Behaviors
 */
	var $actsAs = array(
		'Callbackable',
		'Containable',
	);

/**
 * Number of associations to recurse through during find calls. Fetches only
 * the first level by default.
 *
 * @var integer
 * @access public
 * @link http://book.cakephp.org/view/1057/Model-Attributes#recursive-1063
 */
	var $recursive = -1;

/**
 * Query currently executing.
 *
 * @var array
 * @access public
 */
	var $query = null;

/**
 * Custom find types, as per Matt Curry's method
 *
 * @param string $type
 * @param array $options
 * @return mixed array|integer|boolean
 * @access public
 * @author Matt Curry
 * @link http://github.com/mcurry/find
 */
	function find($type, $options = array()) {
		if (is_array($options)) {
			if (!empty($options['blacklist'])) {
				$options['blacklist'] = (array) $options['blacklist'];
				$options['fields'] = (isset($options['fields'])) ? $options['fields'] : array_keys($this->schema());
				$options['fields'] = array_diff($options['fields'], $options['blacklist']);
				unset($options['blacklist']);
			}
			if (!empty($options['cache'])) {
				if (!class_exists('MiCache')) App::import('Vendor', 'mi_cache');
				if (is_int($options['cache'])) MiCache::config(array('duration' => $options['cache']));
				unset($options['cache']);
				return MiCache::data($this->alias, 'find', $type, $options);
			}
		}

		if (in_array($type, array_keys($this->_findMethods))) {
			$args = func_get_args();
			return call_user_func_array(array('parent', 'find'), $args);
		} else {
		 	$method = (is_string($type)) ? sprintf('__find%s', Inflector::camelize($type)) : null;
			if ($method && method_exists($this, $method)) {
				$return = $this->{$method}($options);
				if ($this->query != null) {
					unset($this->query['paginate']);
					$query = $this->query;
					$this->query = null;
					return $query;
				}
				return $return;
			}
		}

		diebug(array($type, $options));
		$calledFrom = debug_backtrace();
		CakeLog::write('error', "Unknown method {$this->alias}->{$method} in " . substr(str_replace(ROOT, '', $calledFrom[0]['file']), 1) . ' on line ' . $calledFrom[0]['line'] );
		return false;
	}

/**
 * Allows the returning of query parameters for use in pagination
 *
 * @param array $queryData Data used to execute this query, i.e. conditions, order, etc.
 * @return mixed true if the operation should continue, false if it should abort; or, modified
 *               $queryData to continue with new $queryData
 * @access public
 * @author Matt Curry
 * @link http://book.cakephp.org/view/1048/Callback-Methods#beforeFind-1049
 */
	function beforeFind($query = array()) {
		$query = (array) $query;
		if (!empty($query['blacklist'])) {
			$query['blacklist'] = (array) $query['blacklist'];
			$query['fields'] = (isset($query['fields'])) ? $query['fields'] : array_keys($this->schema());
			$query['fields'] = array_diff($query['fields'], $query['blacklist']);
			unset($query['blacklist']);
		}
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
 * Saves model data (based on white-list, if supplied) to the database. By
 * default, validation occurs before save.
 *
 * @param array $data Data to save.
 * @param mixed $validate Either a boolean, or an array.
 *   If a boolean, indicates whether or not to validate before saving.
 *   If an array, allows control of validate, callbacks, and fieldList
 * @param array $fieldList List of fields to allow to be written
 * @param array $extra controls access to optional data a Behavior may want
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @access public
 * @author Jose Diaz-Gonzalez
 * @link http://book.cakephp.org/view/1031/Saving-Your-Data
 **/
	function save($data = null, $validate = true, $fieldList = array(), $extra = array()) {
		$this->data = (!$data) ? $this->data : $data;
		if (!$this->data) return false;

		$options = array('validate' => true, 'fieldList' => array(), 'callbacks' => true);
		if (is_array($validate)) {
			$options = array_merge($options, $validate);
			foreach ($options as $key => &$value) {
				if (!in_array($key, array('validate', 'fieldList', 'callbacks'))) {
					$extra[$key] = $value;
				}
			}
		} else {
			$options = array_merge($options, compact('validate', 'fieldList', 'callbacks'));
		}

		$this->setCallbackableData($extra);

		$method = null;
		if (($callback = $this->getCallbackableData('callback', 'string')) != false) {
			$method = sprintf('__beforeSave%s', Inflector::camelize($callback));
		}

		if ($method && method_exists($this, $method)) {
			$this->data = $this->{$method}($this->data, $extra);
		}
		if (!$this->data) return false;
		return parent::save($this->data, $options);
	}


/**
 * Convenience method to update one record without invoking any callbacks
 *
 * @param array $fields Set of fields and values, indexed by fields.
 *    Fields are treated as SQL snippets, to insert literal values manually escape your data.
 * @param mixed $conditions Conditions to match, true for all records
 * @return boolean True on success, false on Model::id is not set or failure
 * @access public
 * @author Jose Diaz-Gonzalez
 * @link http://book.cakephp.org/view/1031/Saving-Your-Data
 **/
	function update($fields, $conditions = array()) {
		$conditions = (array) $conditions;
		if (!$this->id) return false;

		$conditions = array_merge(array("{$this->alias}.{$this->primaryKey}" => $this->id), $conditions);

		return $this->updateAll($fields, $conditions);
	}

/**
 * Custom Model::paginateCount() method to support custom model find pagination
 *
 * @param array $conditions
 * @param int $recursive
 * @param array $extra
 * @return array
 */
	function paginateCount($conditions = array(), $recursive = 0, $extra = array()) {
		$parameters = compact('conditions');
		if (isset($extra['type'])) {
			$extra['operation'] = 'count';
			return $this->find($extra['type'], array_merge($parameters, $extra));
		} else {
			return $this->find('count', array_merge($parameters, $extra));
		}
	}

/**
 * Removes 'fields' key from count query on custom finds when it is an array,
 * as it will completely break the Model::_findCount() call
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @param array $data
 * @return int The number of records found, or false
 * @access protected
 * @see Model::find()
 */
	function _findCount($state, $query, $results = array()) {
		if ($state == 'before' && isset($query['operation'])) {
			if (!empty($query['fields']) && is_array($query['fields'])) {
				if (!preg_match('/^count/i', $query['fields'][0])) {
					unset($query['fields']);
				}
			}
		}
		return parent::_findCount($state, $query, $results);
	}

/**
 * Disables/detaches all behaviors from model
 *
 * @param mixed $except string or array of behaviors to exclude from detachment
 * @param boolean $detach If true, detaches the behavior instead of disabling it
 * @return void
 * @access public
 * @author Jose Diaz-Gonzalez
 */
	function disableAllBehaviors($except = array(), $detach = false) {
		$behaviors = array_diff($this->Behaviors->attached(), (array) $except);
		foreach ($behaviors as &$behavior) {
			if ($detach) {
				$this->Behaviors->detach($behavior);
			} else {
				$this->Behaviors->disable($behavior);
			}
		}
	}

/**
 * Enables all previously disabled attachments
 *
 * @return void
 * @access public
 * @author Jose Diaz-Gonzalez
 */
	function enableAllBehaviors() {
		$behaviors = $this->Behaviors->attached();
		foreach ($behaviors as &$behavior) {
			if (!$this->Behaviors->enabled($behavior)) {
				$this->Behaviors->enable($behavior);
			}
		}
	}

	function __findLookup($options = array()) {
		if (!is_array($options)) $options = array('conditions' => array($this->displayField => $options));
		$options = array_merge(array(
			'field' => $this->primaryKey,
			'create' => true,
			'conditions' => array()
		), $options);

		if (!empty($options['field'])) {
			$fieldValue = $this->field($options['field'], $options['condition']);
		} else {
			$fieldValue = $this->find('first', $options['conditions']);
		}

		if ($fieldValue !== false) return $fieldValue;
		if ($options['create'] === false) return false;

		$this->create($options['conditions']);
		if (!$this->save()) return false;

		$conditions[$this->primaryKey] = $this->id;
		if (empty($options['field'])) return $this->read();
		return $this->field($options['field'], $options['conditions']);
	}

}
?>