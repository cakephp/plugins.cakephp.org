<?php
class Package extends AppModel {
	var $name = 'Package';
	var $actsAs = array(
		'Searchable.Searchable' => array(
			'summary' => 'description',
			'url' => array(
				'Package' => array('package' => 'name'), 
				'Maintainer' => array('maintainer' => 'username')
			),
		),
		'Taggable'
	);
	var $belongsTo = array('Maintainer');
	var $hasMany = array('PackagesTag');
	var $hasAndBelongsToMany = array('Tag');

	function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->order = "`{$this->alias}`.`{$this->displayField}` asc";
		$this->validate = array(
			'maintainer_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => __('must contain only numbers', true),
				),
			),
			'name' => array(
				'notempty' => array(
					'rule' => array('notempty'),
					'message' => __('cannot be left empty', true),
				),
			),
		);
	}

	function __findAutocomplete($name = null) {
		if (!$name) return false;

		return $this->find('all', array(
			'cache' => true,
			'conditions' => array('Package.name LIKE' => "%{$name}%"),
			'contain' => false,
			'limit' => 10,
			'fields' => array('name')));
	}

	function __findEdit($id = null) {
		if (!$id) return false;

		return $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.{$this->primaryKey}" => $id),
			'contain' => array(
				'Maintainer', 'Tag')));
	}

	function __findLatest() {
		return $this->find('all', array(
			'contain' => array('Maintainer.id', 'Maintainer.username'),
			'fields' => array('id', 'maintainer_id', 'name', 'created'),
			'limit' => 5,
			'order' => "{$this->alias}.created DESC"));
	}

	function __findRandom() {
		$id = $this->find('random_ids', 5);

		return $this->find('all', array(
			'contain' => array('Maintainer.id', 'Maintainer.username'),
			'fields' => array('id', 'maintainer_id', 'name', 'created'),
			'conditions' => array("{$this->alias}.{$this->primaryKey}" => $id)));
	}

	function __findRandomIds($limit = 5) {
		App::import('Vendor', 'mi_cache');
		return MiCache::data($this->alias, 'find', 'list', array(
			'fields' => 'id',
			'order' => 'RAND()',
			'limit' => $limit));
	}

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