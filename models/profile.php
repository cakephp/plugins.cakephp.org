<?php
/**
 * Profile model
 *
 * Copyright 2009 - 2010, Cake Development Corporation
 *                        1785 E. Sahara Avenue, Suite 490-423
 *                        Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 2009 - 2010, Cake Development Corporation
 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * profile model
 *
 * @package		cakepackages
 * @subpackage	cakepackages.models
 */
class Profile extends AppModel {

/**
 * Name
 *
 * @var string $name
 * @access public
 */
	public $name = 'Profile';


/**
 * Behaviors
 *
 * @var array
 * @access public
 */
	public $actsAs = array(
		'Ratings.Ratable' => array());

/**
 * Validation rules - initialized in constructor
 *
 * @var array
 * @access public
 */
	public $validate = array();

/**
 * belongsTo association
 *
 * @var array $belongsTo
 * @access public
 */
	public $belongsTo = array();

/**
 * Allowed values for the "sex" profile field - initialized in constructor
 *
 * @var array
 * @access public
 */
	public $sexValues;

/**
 * Constructor
 *
 * @param mixed $id Model ID
 * @param string $table Table name
 * @param string $ds Datasource
 * @access public
 */
	public function __construct($id = false, $table = null, $ds = null) {
		$userClass = Configure::read('App.UserClass');
		if (empty($userClass)) {
			$userClass = 'User';
		}

		$this->belongsTo['User'] = array(
			'className' => $userClass,
			'foreignKey' => 'user_id');
		parent::__construct($id, $table, $ds);
	}

/**
 * Edits an existing Profile.
 *
 * @param string $id, profile id
 * @param string $userId, user id
 * @param array $data, controller post data usually $this->data
 * @return mixed True on successfully save else post data as array
 * @access public
 */
	public function edit($id = null, $userId = null, $data = null) {
		$conditions = array();
		if (!is_null($id)) {
			$conditions[$this->alias . '.' . $this->primaryKey] = $id;
		}
		if (!is_null($userId)) {
			$conditions[$this->alias . '.user_id'] = $userId;
		}

		if (!empty($conditions)) {
			$profile = $this->find('first', array('contain' => array('User'), 'conditions' => $conditions));
		}

		if (empty($profile)) {
			throw new OutOfBoundsException(__('Invalid Profile', true));
		}
		$this->set($profile);
		if (!empty($data)) {
			$this->set($data);
			$result = $this->save(null, true);
			if ($result) {
				$this->data = $result;
				return true;
			} else {
				return $data;
			}
		} else {
			return $profile;
		}
	}

/**
 * create profile if not exists
 *
 * @param boolean created, true if a new record was created
 * @return void
 * @access public
 */
	public function createIfNotExists($userId) {
		$profile = $this->find('first', array('conditions' => array('Profile.user_id' => $userId)));
		if (empty($profile)) {
			$profile = array($this->alias => array(
				'rating' => 0,
				'user_id' => $userId));
			$this->create($profile);
			$this->save();
		}
	}
	
}
