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
		$this->_findMethods['dashboard'] = true;
		$this->_findMethods['edit'] = true;
		$this->_findMethods['existing'] = true;
		$this->_findMethods['forgotpassword'] = true;
		$this->_findMethods['index'] = true;
		$this->_findMethods['resetpassword'] = true;
		$this->_findMethods['username'] = true;
		$this->_findMethods['view'] = true;
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

	function _findDashboard($state, $query, $results = array()) {
		if ($state == 'before') {
		    $user_id = Authsome::get($this->primaryKey);
			if (empty($user_id)) {
				throw new OutOfBoundsException(__('Invalid user', true));
			}

			$query['contain'] = false;
			$query['conditions'] = array("{$this->alias}.{$this->primaryKey}" => $user_id);
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new OutOfBoundsException(__('Invalid user', true));
			}
			return $results[0];
		}
	}

	function _findEdit($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Invalid maintainer', true));
			}

			$query['contain'] = false;
			$query['conditions'] = array("{$this->alias}.{$this->primaryKey}" => $query[0]);
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new OutOfBoundsException(__('Invalid maintainer', true));
			}
			return $results[0];
		}
	}

	function _findExisting($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Nonexistent maintainer', true));
			}

			$query['contain'] = array('Package');
			$query['conditions'] = array("{$this->alias}.{$this->displayField}" => $query[0]);
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new OutOfBoundsException(__('Nonexistent maintainer', true));
			}
			return $results[0];
		}
	}

	function _findForgotpassword($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Invalid email address', true));
			}

			$query['contain'] = false;
			$query['conditions'] = array("{$this->alias}.email" => $query[0]);
			$query['fields'] = array('id', 'email', 'username');
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new OutOfBoundsException(__('No user found for this email address', true));
			}
			return $results[0];
		}
	}

	function _findIndex($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['fields'] = array('id', 'username', 'name', 'alias', 'url', 'twitter_username', 'company', 'location', 'gravatar_id');
			$query['contain'] = false;
			if (!empty($query['operation'])) {
				return $this->_findCount($state, $query, $results);
			}
			return $query;
		} elseif ($state == 'after') {
		    if (!empty($query['operation'])) {
				return $this->_findCount($state, $query, $results);
			}
			return $results;
		}
	}

	function _findResetpassword($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query['username']) || empty($query['key'])) {
				throw new InvalidArgumentException(__('An error occurred', true));
			}

			$query['contain'] = false;
			$query['conditions'] = array(
				"{$this->alias}.{$this->displayField}" => $query['username'],
				"{$this->alias}.activation_key" => $query['key'],
			);
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new OutOfBoundsException(__('An error occurred', true));
			}
			return $results[0];
		}
	}

	function _findUsername($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Invalid maintainer', true));
			}

			$query['contain'] = false;
			$query['conditions'] = array("{$this->alias}.{$this->displayField}" => $query[0]);
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new OutOfBoundsException(__('Invalid maintainer', true));
			}
			return $results[0];
		}
	}

	function _findView($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Invalid maintainer', true));
			}

			$query['fields'] = array('id', 'username', 'name', 'alias', 'url', 'twitter_username', 'company', 'location', 'gravatar_id');
			$query['cache'] = 3600;
			$query['contain'] = array('Package' => array(
				'fields' => array('maintainer_id', 'name', 'description'),
				'conditions' => array('Package.deleted' => 0)
			));
			$query['conditions'] = array("{$this->alias}.{$this->displayField}" => $query[0]);
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new OutOfBoundsException(__('Invalid maintainer', true));
			}
			return $results[0];
		}
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

	function authsomeLogin($type, $credentials = array()) {
		switch ($type) {
			case 'guest':
				// You can return any non-null value here, if you don't
				// have a guest account, just return an empty array
				return array('guest' => 'guest');
			case 'credentials':
				// This is the logic for validating the login
				$conditions = array(
					"{$this->alias}.email" => $credentials['login'],
					"{$this->alias}.password" => Authsome::hash($credentials['password']),
				);
				break;
			case 'username':
				$conditions = array(
					"{$this->alias}.{$this->displayField}" => $credentials['login'],
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