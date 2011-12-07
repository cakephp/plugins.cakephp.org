<?php
if (!class_exists('User')) {
	App::import('Model', 'Users.User');
}
class PkgUser extends User {

/**
 * Name
 *
 * @var string $name
 */
	public $name = 'PkgUser';

/**
 * Database table used
 *
 * @var string
 */
	public $useTable = 'users';

/**
 * Database config used
 *
 * @var string
 */
	public $useDbConfig = 'cakeusers';

/**
 * Behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'Ratings.Ratable' => array(),
		'Search.Searchable'
	);

/**
 * Find methods
 *
 * @var array
 */
	public $findMethods = array(
		'name' => array(
			'order' => array('PkgUser.username' => 'asc')
		),
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Detail' => array(
			'className' => 'Users.Detail',
			'foreign_key' => 'user_id'
		),
	);

/**
 * hasOne associations
 *
 * @var array
 */
	public $hasOne = array(
		'Profile' => array(
			'className' => 'Profile',
			'foreignKey' => 'user_id'
		)
	);

/**
 * Constructor
 *
 * @param mixed $id Model ID
 * @param string $table Table name
 * @param string $ds Datasource
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

		$rules = array(
			'mandatory' => array(
				'rule' => 'notEmpty',
				'message' => __d('packages', 'This field is required.', true),
				'required' => true,
				'last' => true
			)
		);
	}

/**
 * Returns all data about a user
 *
 * @param string user slug
 * @return array
 */
	public function view($slug = null) {
		$user = $this->find('first', array(
			'conditions' => array(
				$this->alias . '.slug' => $slug
			)
		));

		if (empty($user)) {
			throw new Exception(__d('users', 'The user does not exist.', true));
		}

		if (empty($user['Profile']['id'])) {
			$this->Profile->createIfNotExists($user[$this->alias]['id']);
			$user = $this->find('first', array(
				'contain' => array(
					'Profile'
				),
				'conditions' => array(
					$this->alias . '.slug' => $slug
				)
			));
		}

		return $user;
	}

/**
 * afterFind callback
 *
 * @param array $results Result data
 * @param mixed $primary Primary query
 * @return array
 */
	public function afterFind($results, $primary = false) {
		foreach ($results as &$row) {
			if (isset($row['Detail']) && (is_array($row))) {
				$detail = $this->Detail->getSection($row[$this->alias]['id'], 'User');
				$row['Detail'] = !empty($detail['User']) ? $detail['User'] : array();
				$row[$this->alias] = array_merge($row[$this->alias], $row['Detail']);

				$names = array('firstname' => '', 'lastname' => '');
				foreach ($names as $key => $value) {
					if (empty($row[$this->alias][$key])) {
						unset($names[$key]);
					} else {
						$names[$key] = $row[$this->alias][$key];
					}
				}
				$row[$this->alias]['fullname'] = implode(' ', $names);
			}
		}
		return $results;
	}

/**
 * afterSave callback
 *
 * @param boolean created, true if a new record was created
 * @return void
 */
	public function afterSave($created) {
		if ($created) {
			$this->Profile->createIfNotExists($this->id);
		}
	}

}