<?php
/* SVN FILE: $Id: soft_deletable.php 38 2007-11-26 19:36:27Z mgiglesias $ */

/**
 * SoftDeletable Behavior class file.
 *
 * @filesource
 * @author Mariano Iglesias
 * @link http://cake-syrup.sourceforge.net/ingredients/soft-deletable-behavior/
 * @version	$Revision: 38 $
 * @license	http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package app
 * @subpackage app.models.behaviors
 */

/**
 * Model behavior to support soft deleting records.
 *
 * @package app
 * @subpackage app.models.behaviors
 */
class SoftDeletableBehavior extends ModelBehavior {
/**
 * Contain settings indexed by model name.
 *
 * @var array
 * @access private
 */
	var $__settings = array();

	var $defaults = array(
		'field' => 'deleted',
		'field_date' => 'deleted_date',
		'delete' => true,
		'find' => true
	);

/**
 * Initiate behaviour for the model using settings.
 *
 * @param object $model Model using the behaviour
 * @param array $settings Settings to override for model.
 */
	public function setup(Model $model, $settings = array()) {
		if (!is_array($settings)) $settings = array();
		$this->__settings[$model->alias] = array_merge($this->defaults, $settings);
	}

/**
 * Run before a model is deleted, used to do a soft delete when needed.
 *
 * @param object $model Model about to be deleted
 * @param boolean $cascade If true records that depend on this record will also be deleted
 * @return boolean Set to true to continue with delete, false otherwise
 */
	public function beforeDelete(Model $model, $cascade = true) {
		if (!$this->__settings[$model->alias]['delete'] || !$model->hasField($this->__settings[$model->alias]['field'])) {
			return true;
		}

		return !$this->softDelete($model, $model->id, $cascade);
	}

/**
 * Permanently deletes a record.
 *
 * @param object $model Model from where the method is being executed.
 * @param mixed $id ID of the soft-deleted record.
 * @param boolean $cascade Also delete dependent records
 * @return boolean Result of the operation.
 */
	function hardDelete(Model $model, $id, $cascade = true) {
		$onFind = $this->__settings[$model->alias]['find'];
		$onDelete = $this->__settings[$model->alias]['delete'];
		$this->enableSoftDeletable($model, false);

		$deleted = $model->delete($id, $cascade);

		$this->enableSoftDeletable($model, 'delete', $onDelete);
		$this->enableSoftDeletable($model, 'find', $onFind);

		return $deleted;
	}

	function softDelete(Model $model, $id = null, $cascade = true) {
		if (!$id) {
			return false;
		}

		$model->id = $id;
		$attributes = $this->__settings[$model->alias];

		$data = array($model->alias => array(
			$attributes['field'] => 1
		));

		if (isset($attributes['field_date']) && $model->hasField($attributes['field_date'])) {
			$data[$model->alias][$attributes['field_date']] = date('Y-m-d H:i:s');
		}

		foreach (array_merge(array_keys($data[$model->alias]), array('field', 'field_date', 'find', 'delete')) as $field) {
			unset($attributes[$field]);
		}

		if (!empty($attributes)) {
			$data[$model->alias] = array_merge($data[$model->alias], $attributes);
		}

		$model->id = $id;
		$deleted = $model->save($data, false, array_keys($data[$model->alias]));
		if (!$deleted) {
			return false;
		}

		if ($cascade) {
			$model->_deleteDependent($id, $cascade);
			$model->_deleteLinks($id);
		}

		return true;
	}

/**
 * Permanently deletes all records that were soft deleted.
 *
 * @param object $model Model from where the method is being executed.
 * @param boolean $cascade Also delete dependent records
 * @return boolean Result of the operation.
 */
	function purge(Model $model, $cascade = true) {
		$purged = false;

		if ($model->hasField($this->__settings[$model->alias]['field'])) {
			$onFind = $this->__settings[$model->alias]['find'];
			$onDelete = $this->__settings[$model->alias]['delete'];
			$this->enableSoftDeletable($model, false);

			$purged = $model->deleteAll(array($this->__settings[$model->alias]['field'] => '1'), $cascade);

			$this->enableSoftDeletable($model, 'delete', $onDelete);
			$this->enableSoftDeletable($model, 'find', $onFind);
		}

		return $purged;
	}

/**
 * Restores a soft deleted record, and optionally change other fields.
 *
 * @param object $model Model from where the method is being executed.
 * @param mixed $id ID of the soft-deleted record.
 * @param $attributes Other fields to change (in the form of field => value)
 * @return boolean Result of the operation.
 */
	function undelete(Model $model, $id = null, $attributes = array()) {
		if ($model->hasField($this->__settings[$model->alias]['field'])) {
			if (empty($id)) {
				$id = $model->id;
			}

			$data = array($model->alias => array(
				$model->primaryKey => $id,
				$this->__settings[$model->alias]['field'] => '0'
			));

			if (isset($this->__settings[$model->alias]['field_date'])
			&& $model->hasField($this->__settings[$model->alias]['field_date'])) {
				$data[$model->alias][$this->__settings[$model->alias]['field_date']] = null;
			}

			if (!empty($attributes)) {
				$data[$model->alias] = array_merge($data[$model->alias], $attributes);
			}

			$onFind = $this->__settings[$model->alias]['find'];
			$onDelete = $this->__settings[$model->alias]['delete'];
			$this->enableSoftDeletable($model, false);

			$model->id = $id;
			$result = $model->save($data, false, array_keys($data[$model->alias]));

			$this->enableSoftDeletable($model, 'find', $onFind);
			$this->enableSoftDeletable($model, 'delete', $onDelete);

			return ($result !== false);
		}

		return false;
	}

/**
 * Set if the beforeFind() or beforeDelete() should be overriden for specific model.
 *
 * @param object $model Model about to be deleted.
 * @param mixed $methods If string, method (find / delete) to enable on, if array array of method names, if boolean, enable it for find method
 * @param boolean $enable If specified method should be overriden.
 */
	function enableSoftDeletable(Model $model, $methods, $enable = true) {
		if (is_bool($methods)) {
			$enable = $methods;
			$methods = array('find', 'delete');
		}

		if (!is_array($methods)) {
			$methods = array($methods);
		}

		foreach ($methods as $method) {
			$this->__settings[$model->alias][$method] = $enable;
		}
	}

/**
 * Run before a model is about to be find, used only fetch for non-deleted records.
 *
 * @param object $model Model about to be deleted.
 * @param array $query Data used to execute this query, i.e. conditions, order, etc.
 * @return mixed Set to false to abort find operation, or return an array with data used to execute query
 */
	public function beforeFind(Model $model, $query) {
		if ($this->__settings[$model->alias]['find'] && $model->hasField($this->__settings[$model->alias]['field'])) {
			$Db = ConnectionManager::getDataSource($model->useDbConfig);
			$include = false;

			if (!empty($query['conditions']) && is_string($query['conditions'])) {
				$include = true;

				$fields = array(
					$Db->name($model->alias) . '.' . $Db->name($this->__settings[$model->alias]['field']),
					$Db->name($this->__settings[$model->alias]['field']),
					$model->alias . '.' . $this->__settings[$model->alias]['field'],
					$this->__settings[$model->alias]['field']
				);

				foreach ($fields as $field) {
					if (preg_match('/^' . preg_quote($field) . '[\s=!]+/i', $query['conditions']) || preg_match('/\\x20+' . preg_quote($field) . '[\s=!]+/i', $query['conditions']))
					{
						$include = false;
						break;
					}
				}
			}
			else if (empty($query['conditions'])
			|| (!in_array($this->__settings[$model->alias]['field'], array_keys($query['conditions']))
			&& !in_array($model->alias . '.' . $this->__settings[$model->alias]['field'], array_keys($query['conditions'])))) {
				$include = true;
			}

			if ($include) {
				if (empty($query['conditions'])) {
					$query['conditions'] = array();
				}

				if (is_string($query['conditions'])) {
					$query['conditions'] = $Db->name($model->alias) . '.' . $Db->name($this->__settings[$model->alias]['field']) . '!= 1 AND ' . $query['conditions'];
				} else {
					$query['conditions'][$model->alias . '.' . $this->__settings[$model->alias]['field']] = 0;
				}
			}
		}

		return $query;
	}

/**
 * Run before a model is saved, used to disable beforeFind() override.
 *
 * @param object $model Model about to be saved.
 * @return boolean True if the operation should continue, false if it should abort
 */
	public function beforeSave(Model $model, $options = array()) {
		if ($this->__settings[$model->alias]['find']) {
			if (!isset($this->__backAttributes)) {
				$this->__backAttributes = array($model->alias => array());
			} else if (!isset($this->__backAttributes[$model->alias])) {
				$this->__backAttributes[$model->alias] = array();
			}

			$this->__backAttributes[$model->alias]['find'] = $this->__settings[$model->alias]['find'];
			$this->__backAttributes[$model->alias]['delete'] = $this->__settings[$model->alias]['delete'];
			$this->enableSoftDeletable($model, false);
		}

		return true;
	}

/**
 * Run after a model has been saved, used to enable beforeFind() override.
 *
 * @param object $model Model just saved.
 * @param boolean $created True if this save created a new record
 */
	public function afterSave(Model $model, $created, $options = array()) {
		if (isset($this->__backAttributes[$model->alias]['find'])) {
			$this->enableSoftDeletable($model, 'find', $this->__backAttributes[$model->alias]['find']);
			$this->enableSoftDeletable($model, 'delete', $this->__backAttributes[$model->alias]['delete']);
			unset($this->__backAttributes[$model->alias]['find']);
			unset($this->__backAttributes[$model->alias]['delete']);
		}
	}

}
