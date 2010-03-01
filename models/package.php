<?php
class Package extends AppModel {
	var $name = 'Package';
	var $actsAs = array(
		'Searchable.Searchable' => array(
			'summary' => 'description',
			'url' => array(
				'Package' => array('package' => 'name'), 
				'Maintainer' => array('maintainer' => 'username'))),
		'Taggable');
	var $order = array('`Package`.`name` asc');
	var $validate = array(
		'maintainer_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
			),
		),
	);
	//The Associations below have been created with all possible keys, those that are not needed can be removed

	var $belongsTo = array(
		'Maintainer' => array(
			'className' => 'Maintainer',
			'foreignKey' => 'maintainer_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

	var $hasMany = array(
		'PackagesTags' => array(
			'className' => 'PackagesTags',
			'foreignKey' => 'package_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

	var $hasAndBelongsToMany = array(
		'Tag' => array(
			'className' => 'Tag',
			'joinTable' => 'packages_tags',
			'foreignKey' => 'package_id',
			'associationForeignKey' => 'tag_id',
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

	function __findView($params = array()) {
		if (!isset($params['maintainer']) || !isset($params['package'])) return false;

		$maintainer_id = $this->Maintainer->find('maintainer_id', $params['maintainer']);

		if (!$maintainer_id) return false;

		return $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.{$this->displayField}" => $params['package'],
				"{$this->alias}.maintainer_id" => $maintainer_id),
			'contain' => array(
				'Maintainer', 'Tag')));
	}

	function __findEdit($id = null) {
		if (!$id) return false;

		return $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.{$this->primaryKey}" => $id),
			'contain' => array(
				'Maintainer', 'Tag')));
	}

	function getSearchableData($data) {
		$searchableData = array();
		foreach ($data as $modelName => $modelData) {
			foreach ($modelData as $field => $value) {
				$searchableData["{$modelName}.{$field}"] = $value;
			}
		}
		return $searchableData;
	}
}
?>