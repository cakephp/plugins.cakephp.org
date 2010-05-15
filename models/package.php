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
			'conditions' => array("{$this->alias}.{$this->displayField} LIKE" => "%{$name}%"),
			'contain' => false,
			'limit' => 10,
			'fields' => array($this->primaryKey, $this->displayField)));
	}

	function __findEdit($id = null) {
		if (!$id) return false;

		return $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.{$this->primaryKey}" => $id),
			'contain' => array(
				'Maintainer', 'Tag')));
	}

	function __findIndex($params = array()) {
		$options = array_merge(array(
							'contain' => array('Maintainer'),
							'limit' => 10,
							'paginate' => true),
							$params['paginate']);

		if ($params['type']) $options['conditions'] = array("{$this->alias}.contains_{$params['type']}" => true);

		return $this->find('all', $options);
	}

	function __findLatest() {
		return $this->find('all', array(
			'cache' => 3600,
			'contain' => array('Maintainer.id', 'Maintainer.username'),
			'fields' => array($this->primaryKey, $this->displayField, 'maintainer_id', 'created'),
			'limit' => 5,
			'order' => "{$this->alias}.created DESC"));
	}

	function __findRandom() {
		$id = $this->find('random_ids', 5);

		return $this->find('all', array(
			'cache' => 600,
			'contain' => array('Maintainer.id', 'Maintainer.username'),
			'fields' => array($this->primaryKey, $this->displayField, 'maintainer_id', 'created'),
			'conditions' => array("{$this->alias}.{$this->primaryKey}" => $id)));
	}

	function __findRandomIds($limit = 5) {
		App::import('Vendor', 'mi_cache');
		return MiCache::data($this->alias, 'find', 'list', array(
			'fields' => array($this->primaryKey),
			'order' => 'RAND()',
			'limit' => $limit));
	}

	function __findView($params = array()) {
		if (!isset($params['maintainer']) || !isset($params['package'])) return false;

		$maintainer_id = $this->Maintainer->find('maintainer_id', $params['maintainer']);

		if (!$maintainer_id) return false;

		return $this->find('first', array(
			'cache' => 3600,
			'conditions' => array(
				"{$this->alias}.{$this->displayField}" => $params['package'],
				"{$this->alias}.maintainer_id" => $maintainer_id),
			'contain' => array(
				'Maintainer' => array(
					'fields' => array(
						'username',
						'name'
					)
				)
			)
		));
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