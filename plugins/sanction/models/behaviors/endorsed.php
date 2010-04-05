<?php
class Endorsed extends ModelBehavior {

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
 * Initiate Endorsed Behavior
 *
 * @param object $model
 * @param array $config
 * @return void
 * @access public
 */
	function setup(&$model, $config = array()) {
		$config = array_merge(
			array(
				'exception' => array(
					'group' => 'admin',
				),
				'model_field' => "{$model->alias}_{$model->primaryKey}",
				'authsome_field' => 'id',
			),
			$config
		);

		$this->settings[$model->alias] = $config;
	}

	function beforeSave(&$model) {
		if (!isset($model->id)) return true;

		$settings = $this->settings[$model->alias];
		$count = count($settings['exception']);
		$user = Authsome::get();

		if (!$user) return false;

		foreach ($settings['exception'] as $key => $value) {
			if ($user[$model->alias][$key] == $value) {
				$count--;
			}
		}

		if ($count == 0) return true;

		$rec = $model->find('first', array(
			'conditions' => array(
				"{$model->alias}.{$settings['model_field']}" => $model->id),
			'contain' => false));
		return $rec[$model->alias][$settings['model_field']] == Authsome::get('authsome_field')
	}

}
?>