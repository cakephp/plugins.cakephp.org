<?php
class Maintainer extends AppModel {
	var $name = 'Maintainer';
	var $displayField = 'username';
	var $hasMany = array('Package');

	function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->order = "`{$this->alias}`.`{$this->displayField}` asc";
		$this->validate = array(
			'username' => array(
				'required' => array(
					'rule' => array('notempty'),
					'message' => __('cannot be left empty', true),
				),
				'alphanumeric' => array(
					'rule' => array('alphanumeric'),
					'message' => __('must contain only letters and numbers', true),
				),
			),
			'twitter_username' => array(
				'alphanumeric' => array(
					'rule' => array('alphanumeric'),
					'message' => __('must contain only letters and numbers', true),
					'allowEmpty' => true,
				),
			),
		);
	}

/**
 * Ensure the uri is valid if it's been specified
 *
 * @return void
 * @access public
 */
	function beforeSave() {
		if (!empty($this->data[$this->alias]['url'])) {
			if (!strpos($this->data[$this->alias]['url'], '://')) {
				$this->data[$this->alias]['url'] = 'http://' . $this->data[$this->alias]['url'];
			}
		}
		return true;
	}

	function __beforeSaveChangePassword($data, $extra) {
		if (!$data || !isset($data[$this->alias])) return false;

		$data = array(
			$this->alias => array(
				'password' => $data[$this->alias]['password'],
				'new_password' => $data[$this->alias]['new_password'],
				'new_password_confirm' => $data[$this->alias]['new_password_confirm']));

		if ($data[$this->alias]['new_password'] != $data[$this->alias]['new_password_confirm']) return false;
		foreach ($data[$this->alias] as $key => &$value) {
			$value = Security::hash($value, null, true);
			if ($value == Security::hash('', null, true)) {
				return false;
			}
		}
		$data[$this->alias][$this->primaryKey] = Authsome::get($this->primaryKey);

		$user = $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.{$this->primaryKey}" => Authsome::get($this->primaryKey),
				"{$this->alias}.password" => $data[$this->alias]['password']),
			'contain' => false,
			'fields' => array($this->primaryKey)));

		if (!$user) return false;
		return $data;
	}

	function __beforeSaveResetPassword($data, $extra) {
		return array($this->alias => array(
			$this->primaryKey => $extra['user_id'],
			'password' => Authsome::hash($data[$this->alias]['password']),
			'activation_key' => md5(uniqid())));
	}

	function __findByUsername($username = false) {
		if (!$username) return false;

		return $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.{$this->displayField}" => $username),
			'contain' => false));
	}

	function __findDashboard() {
		return $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.{$this->primaryKey}" => Authsome::get($this->primaryKey)),
			'contain' => false));
	}

	function __findExisting($username = false) {
		if (!$username) return false;

		return $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.{$this->displayField}" => $username),
			'contain' => array(
				'Package')));
	}

	function __findForgotPassword($email = null) {
		if (!$email) return false;

		return $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.email" => $email),
			'contain' => false));
	}

	function __findIndex($paginate = array()) {
		$options = array_merge(array(
				'blacklist' => array('group', 'email', 'password', 'activation_key', 'created', 'modified'),
				'paginate' => true
			),
			$paginate
		);

		return $this->find('all', $options);
	}

	function __findMaintainerId($username = null) {
		if (!$username) return false;

		$maintainer = $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.{$this->displayField}" => $username),
			'contain' => false));

		return ($maintainer) ? $maintainer[$this->alias][$this->primaryKey] : false;
	}

	function __findResetPassword($options = array()) {
		if (!isset($options['username']) || !isset($options['key'])) return false;

		return $this->find('first', array(
			'conditions' => array(
				"{$this->alias}.{$this->displayField}" => $options['username'],
				"{$this->alias}.activation_key" => $options['key'])));
	}


	function __findView($username = null) {
		if (!$username) return false;

		return $this->find('first', array(
			'cache' => 3600,
			'conditions' => array(
				"{$this->alias}.{$this->displayField}" => $username),
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
		$data = array(
			"{$this->alias}" => array(
				"{$this->primaryKey}" => $id,
				'activation_key' => $activationKey,
			),
		);

		if (!$this->save($data, array('callbacks' => false))) return false;
		return $activationKey;
	}
}
?>