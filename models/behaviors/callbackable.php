<?php
class CallbackableBehavior extends ModelBehavior {

/**
 * Data that is available within the behavior
 *
 * @var string
 * @access private
 **/
	var $__data = null;

/**
 * Convenience function to set extra data for use within callbacks
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 * @access public
 **/
	function setCallbackableData(&$model, $data = null) {
		$this->__data = $data;
		return $this->__data;
	}

/**
 * Convenience function to get extra data for use within callbacks
 *
 * Can also optionally check for the type of the returned data and return null
 * if the type does not match
 *
 * @param string $key Optional Key potentially pointing to some data
 * @param string $type Optional Enforces a type constraint on returned data
 * @return mixed contents of the key if isset, null otherwise.
 * @author Jose Diaz-Gonzalez
 * @access public
 **/
	function getCallbackableData(&$model, $key = null, $type = null) {
		if (!$key) return $this->__data;

		if (!empty($this->__data[$key])) {
			if (!$type) return $this->__data[$key];

			if (gettype($this->__data[$key]) === $type) return $this->__data[$key];
		}
		return null;
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
		if (($callback = $this->getCallbackableData($model, 'callback', 'string')) != false) {
			$method = sprintf('__beforeValidate%s', Inflector::camelize($callback));
		}

		if ($method && method_exists($model, $method)) {
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
		if (($callback = $this->getCallbackableData($model, 'callback', 'string')) != false) {
			$method = sprintf('__afterSave%s', Inflector::camelize($callback));
		}

		if ($method && method_exists($model, $method)) {
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
		if (($callback = $this->getCallbackableData($model, 'callback', 'string')) != false) {
			$method = sprintf('__beforeDelete%s', Inflector::camelize($callback));
		}

		if ($method && method_exists($model, $method)) {
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
		if (($callback = $this->getCallbackableData($model, 'callback', 'string')) != false) {
			$method = sprintf('__afterDelete%s', Inflector::camelize($callback));
		}

		if ($method && method_exists($model, $method)) {
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
		if (($callback = $this->getCallbackableData($model,'callback', 'string')) != false) {
			$method = sprintf('__onError%s', Inflector::camelize($callback));
		}

		if ($method && method_exists($model, $method)) {
			$model->{$method}($model, $error);
		}
	}
}
?>