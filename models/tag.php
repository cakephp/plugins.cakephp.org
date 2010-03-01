<?php
class Tag extends AppModel {
	var $name = 'Tag';
	var $validate = array(
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
	);
	//The Associations below have been created with all possible keys, those that are not needed can be removed

	var $hasAndBelongsToMany = array(
		'Package' => array(
			'className' => 'Package',
			'joinTable' => 'packages_tags',
			'foreignKey' => 'tag_id',
			'associationForeignKey' => 'package_id',
			'unique' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);

	function __findIndex($name = null) {
		if (!$name) return false;
		return $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.{$this->displayField}" => $name),
			'contain' => array('Package.id' => array('Maintainer.username'), 'Package.name', 'Package.description')));
	}
}
?>