<?php
class CallbackableBehavior extends ModelBehavior {

/**
 * Contains configuration settings for use with individual model objects.
 * Individual model settings should be stored as an associative array, 
 * keyed off of the model name.
 *
 * @var array
 * @access public
 * @see Model::$alias
 */
	var $settings = array();

/**
 * Initiate Callbackable Behavior
 *
 * @param object $model
 * @param array $config
 * @return void
 * @access public
 */
	function setup(&$model, $config = array()) {

	}

	/* -- All possible behavior callbacks have been stubbed out. Remove those you do not need. -- */

/**
 * Before find callback
 *
 * @param object $model Model using this behavior
 * @param array $queryData Data used to execute this query, i.e. conditions, order, etc.
 * @return boolean True if the operation should continue, false if it should abort
 * @access public
 */
	function beforeFind(&$model, $query) { 
		return true;
	}

/**
 * After find callback. Can be used to modify any results returned by find and findAll.
 *
 * @param object $model Model using this behavior
 * @param mixed $results The results of the find operation
 * @param boolean $primary Whether this model is being queried directly (vs. being queried as an association)
 * @return mixed Result of the find operation
 * @access public
 */
	function afterFind(&$model, $results, $primary) { 
		return $results;
	}

/**
 * Before validate callback
 *
 * @param object $model Model using this behavior
 * @return boolean True if validate operation should continue, false to abort
 * @access public
 */
	function beforeValidate(&$model) {
		$method = null;
		if (isset($model->behaviorData['callback']) and is_string($model->behaviorData['callback'])) {
			$method = sprintf('__beforeValidate%s', Inflector::camelize($model->behaviorData['callback']));
		}

		if($method && method_exists($model, $method)) {
			return $model->{$method}($model);
		}
		return true;
	}

/**
 * After save callback
 *
 * @param object $model Model using this behavior
 * @param boolean $created True if this save created a new record
 * @access public
 * @return boolean True if the operation succeeded, false otherwise
 */
	function afterSave(&$model, $created) {
		$method = null;
		if (isset($model->behaviorData['callback']) and is_string($model->behaviorData['callback'])) {
			$method = sprintf('__afterSave%s', Inflector::camelize($model->behaviorData['callback']));
		}

		if($method && method_exists($model, $method)) {
			return $model->{$method}($data);
		}
		return true;
	}

/**
 * Before delete callback
 *
 * @param object $model Model using this behavior
 * @param boolean $cascade If true records that depend on this record will also be deleted
 * @return boolean True if the operation should continue, false if it should abort
 * @access public
 */
	function beforeDelete(&$model, $cascade = true) {
		$method = null;
		if (isset($model->behaviorData['callback']) and is_string($model->behaviorData['callback'])) {
			$method = sprintf('__beforeDelete%s', Inflector::camelize($model->behaviorData['callback']));
		}

		if($method && method_exists($model, $method)) {
			return $model->{$method}($model, $cascade);
		}
		return true;
	}

/**
 * After delete callback
 *
 * @param object  Model using this behavior
 * @access public
 */
	function afterDelete(&$model) {
		$method = null;
		if (isset($model->behaviorData['callback']) and is_string($model->behaviorData['callback'])) {
			$method = sprintf('__afterDelete%s', Inflector::camelize($model->behaviorData['callback']));
		}

		if($method && method_exists($model, $method)) {
			$model->{$method}($model);
		}
	}

/**
 * DataSource error callback
 *
 * @param object $model Model using this behavior
 * @param string $error Error generated in DataSource
 * @access public
 */
	function onError(&$model, $error) {
		$method = null;
		if (isset($model->behaviorData['callback']) and is_string($model->behaviorData['callback'])) {
			$method = sprintf('__onError%s', Inflector::camelize($model->behaviorData['callback']));
		}

		if($method && method_exists($model, $method)) {
			$model->{$method}($model, $error);
		}
	}
}
?>