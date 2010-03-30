<?php
class Tag extends AppModel {
	var $name = 'Tag';
	var $actsAs = array('Tree');
	var $hasAndBelongsToMany = array('Package');

	function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->validate = array(
			'name' => array(
				'notempty' => array(
					'rule' => array('notempty'),
					'message' => __('cannot be left empty', true),
				),
			),
		);
	}

	function __findView($name = null) {
		if (!$name) return false;

		return $this->find('threaded', array(
			'conditions' => array(
				"{$this->alias}.{$this->displayField}" => $name),
				'recursive' => 2));
	}
}
?>