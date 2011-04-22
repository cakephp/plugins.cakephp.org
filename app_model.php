<?php
/**
 * Application Model class
 *
 * Add your application-wide methods to the class, your models will inherit them.
 *
 * @package       app
 */
App::import('Lib', 'LazyModel.LazyModel');
class AppModel extends LazyModel {
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
		'CakeDjjob.CakeDjjob',
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

		if ($recursive != $this->recursive) {
			$parameters['recursive'] = $recursive;
		}

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