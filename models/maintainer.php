<?php
class Maintainer extends AppModel {
	var $name = 'Maintainer';
	function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->order = '`Maintainer`.`username` asc';
		$this->validate = array(
			'username' => array(
				'required' => array(
					'rule' => array('notempty'),
					'message' => __('cannot be left empty', true)
				),
				'alphanumeric' => array(
					'rule' => array('alphanumeric'),
					'message' => __('must only contain letters and numbers', true)
				),
			),
			'twitter_username' => array(
				'alphanumeric' => array(
					'rule' => array('alphanumeric'),
					'message' => __('must only contain letters and numbers', true),
					'allowEmpty' => true,
				),
			),
		);
	}
	//The Associations below have been created with all possible keys, those that are not needed can be removed

	var $hasMany = array(
		'Package' => array(
			'className' => 'Package',
			'foreignKey' => 'maintainer_id',
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

	function __findByName($username = false) {
		if (!$username) return false;

		return $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.username" => $username),
			'contain' => false));
	}

	function __findExisting($username = false) {
		if (!$username) return false;

		return $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.username" => $username),
			'contain' => array(
				'Package')));
	}

	function __findMaintainerId($username = null) {
		if (!$username) return false;

		$maintainer = $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.username" => $username),
			'contain' => false));

		return ($maintainer) ? $maintainer[$this->alias][$this->primaryKey] : false;
	}

	function __findView($username = null) {
		if (!$username) return false;

		return $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.username" => $username),
			'contain' => array(
				'Package')));
	}

}
?>