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

	function __beforeSaveChangePassword($data, $extra) {
		if (!$data || !isset($data[$this->alias])) return false;

		$data = array(
			$this->alias => array(
				'password' => $data[$this->alias]['password'],
				'new_password' => $data[$this->alias]['new_password'],
				'new_password_confirm' => $data[$this->alias]['new_password_confirm']));

		if ($data[$this->alias]['new_password'] != $data[$this->alias]['new_password_confirm']) return false;
		foreach($data[$this->alias] as $key => &$value) {
			$value = Security::hash($value, null, true);
			if ($value == Security::hash('', null, true)) {
				return false;
			}
		}
		$data[$this->alias][$this->primaryKey] = Authsome::get('id');

		$user = $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.id" => Authsome::get('id'),
				"{$this->alias}.password" => $data[$this->alias]['password']),
			'contain' => false,
			'fields' => array('id')));

		if (!$user) return false;
		return $data;
	}

	function __beforeSaveResetPassword($data, $extra) {
		return array($this->alias => array(
			$this->primaryKey => $extra['user_id'],
			'password' => Authsome::hash($data[$this->alias]['password']),
			'activation_key' => md5(uniqid())));
	}

	function __findDashboard() {
		return $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.{$this->primaryKey}" => Authsome::get('id')),
			'contain' => false));
	}

	function __findByUsername($username = false) {
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

	function __findResetPassword($options = array()) {
		if (!isset($options['username']) || !isset($options['key'])) return false;

		return $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.username" => $options['username'],
				"{$this->alias}.activation_key" => $options['key'])));
	}


	function __findView($username = null) {
		if (!$username) return false;

		return $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.username" => $username),
			'contain' => array(
				'Package')));
	}

	function authsomeLogin($type, $credentials = array()) {
		switch ($type) {
			case 'guest':
				// You can return any non-null value here, if you don't
				// have a guest account, just return an empty array
				return array('guest' => 'guest');
			case 'credentials':
				// This is the logic for validating the login
				$conditions = array(
					"{$this->alias}.email" => $credentials['email'],
					"{$this->alias}.password" => Authsome::hash($credentials['password']),
				);
				break;
			case 'cookie':
				list($token, $maintainerId) = split(':', $credentials['token']);
				$duration = $credentials['duration'];

				$loginToken = $this->LoginToken->find('first', array(
					'conditions' => array(
						'user_id' => $maintainerId,
						'token' => $token,
						'duration' => $duration,
						'used' => false,
						'expires <=' => date('Y-m-d H:i:s', strtotime($duration)),
					),
					'contain' => false
				));

				if (!$loginToken) {
					return false;
				}

				$loginToken['LoginToken']['used'] = true;
				$this->LoginToken->save($loginToken);

				$conditions = array(
					"{$this->alias}.{$this->primaryKey}" => $loginToken['LoginToken']['user_id'],
				);
				break;
			default:
				return null;
		}

		$maintainer = $this->find('first', compact('conditions'));
		if (!$maintainer) {
			return false;
		}
		$maintainer[$this->alias]['loginType'] = $type;
		return $maintainer;
	}

	function authsomePersist($maintainer, $duration) {
		$token = md5(uniqid(mt_rand(), true));
		$maintainerId = $maintainer[$this->alias][$this->primaryKey];

		$this->LoginToken->create(array(
			'user_id' => $maintainerId,
			'token' => $token,
			'duration' => $duration,
			'expires' => date('Y-m-d H:i:s', strtotime($duration)),
		));
		$this->LoginToken->save();

		return "${token}:${maintainerId}";
	}

	function changeActivationKey($id) {
		$activationKey = md5(uniqid());
		if (!$this->updateAll(array('activation_key', $activationKey), array("{$this->alias}.{$this->primaryKey}" => $id))) return false;
		return $activationKey;
	}
}
?>