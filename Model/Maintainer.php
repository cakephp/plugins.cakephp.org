<?php
App::uses('Sanitize', 'Utility');
App::uses('ForgotPasswordJob', 'Lib/Job');

class Maintainer extends AppModel {

/**
 * Name of the model.
 *
 * @var string
 * @link http://book.cakephp.org/view/1057/Model-Attributes#name-1068
 */
	public $name = 'Maintainer';

/**
 * Custom display field name. Display fields are used by Scaffold, in SELECT boxes' OPTION elements.
 *
 * @var string
 * @link http://book.cakephp.org/view/1057/Model-Attributes#displayField-1062
 */
	public $displayField = 'username';

/**
 * Detailed list of hasMany associations.
 *
 * @var array
 * @link http://book.cakephp.org/view/1043/hasMany
 */
	public $hasMany = array('Package');

/**
 * Override the constructor to provide custom model finds
 * and validation rule internationalization
 *
 * @param mixed $id Set this ID for this model on startup, can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->order = "`{$this->alias}`.`{$this->displayField}` asc";
		$this->validate = array(
			'username' => array(
				'alphanumeric' => array(
					'rule' => '/[\w_-]+/i',
					'message' => __('must contain only letters and numbers'),
				),
				'isUnique' => array(
					'rule' => array('isUnique'),
					'message' => __('must be a unique maintainer'),
				),
				'required' => array(
					'rule' => array('notempty'),
					'message' => __('cannot be left empty'),
				),
			),
			'twitter_username' => array(
				'alphanumeric' => array(
					'rule' => array('alphanumeric'),
					'message' => __('must contain only letters and numbers'),
					'allowEmpty' => true,
				),
			),
		);
		$this->findMethods['existing'] = true;
		$this->findMethods['forgotpassword'] = true;
		$this->findMethods['index'] = true;
		$this->findMethods['resetpassword'] = true;
		$this->findMethods['username'] = true;
		$this->findMethods['user'] = true;
		$this->findMethods['view'] = true;
	}

/**
 * Finds a given maintainer by name as well as their packages
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @return mixed array of results or false if none found
 * @return array
 */
	public function _findExisting($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Nonexistent user'));
			}

			$query['contain'] = array('Package');
			$query['conditions'] = array("{$this->alias}.{$this->displayField}" => $query[0]);
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new OutOfBoundsException(__('Nonexistent user'));
			}
			return $results[0];
		}
	}

/**
 * Finds a user by email for reseting their password
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @return mixed array of results or false if none found
 * @return array
 */
	public function _findForgotpassword($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Invalid email address'));
			}

			$query['contain'] = false;
			$query['conditions'] = array("{$this->alias}.email" => $query[0]);
			$query['fields'] = array('id', 'email', 'username');
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new OutOfBoundsException(__('No user found for this email address'));
			}
			return $results[0];
		}
	}

/**
 * Finds maintainers for pagination
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @return mixed array of results or false if none found
 * @return array
 */
	public function _findIndex($state, $query, $results = array()) {
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

/**
 * Finds a user by name/key for resetting of the password
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @return mixed array of results or false if none found
 * @return array
 */
	public function _findResetpassword($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query['username']) || empty($query['key'])) {
				throw new InvalidArgumentException(__('An error occurred'));
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
				throw new OutOfBoundsException(__('An error occurred'));
			}
			return $results[0];
		}
	}

/**
 * Finds the current user for the dashboard
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @return mixed array of results or false if none found
 * @return array
 */
	public function _findUser($state, $query, $results = array()) {
		if ($state == 'before') {
			$user_id = false;
			if (!empty($query[0])) {
				$user_id = $query[0];
			} elseif (!empty($query['id'])){
				$user_id = $query['id'];
			} else {
				$user_id = Authsome::get($this->primaryKey);
			}

			if (empty($user_id)) {
				throw new OutOfBoundsException(__('Invalid maintainer'));
			}

			$query['contain'] = false;
			$query['conditions'] = array("{$this->alias}.{$this->primaryKey}" => $user_id);
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new OutOfBoundsException(__('Invalid user'));
			}
			return $results[0];
		}
	}

/**
 * Finds a user by username
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @return mixed array of results or false if none found
 * @return array
 */
	public function _findUsername($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Invalid maintainer'));
			}

			$query['contain'] = false;
			$query['conditions'] = array("{$this->alias}.{$this->displayField}" => $query[0]);
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new OutOfBoundsException(__('Invalid maintainer'));
			}
			return $results[0];
		}
	}

/**
 * Finds a user by name for the /maintainers/view action
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @return mixed array of results or false if none found
 * @return array
 */
	public function _findView($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Invalid maintainer'));
			}

			$query['fields'] = array('id', 'username', 'name', 'alias', 'url', 'twitter_username', 'company', 'location', 'gravatar_id');
			$query['contain'] = array('Package' => array(
				'conditions' => array('Package.deleted' => 0),
				'order' => array('Package.last_pushed_at desc'),
			));
			$query['conditions'] = array("{$this->alias}.{$this->displayField}" => $query[0]);
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new OutOfBoundsException(__('Invalid maintainer'));
			}

			return $results[0];
		}
	}

/**
 * Ensure the uri is valid if it's been specified
 *
 * @return void
 */
	public function beforeSave() {
		if (!empty($this->data[$this->alias]['url'])) {
			if (!strpos($this->data[$this->alias]['url'], '://')) {
				$this->data[$this->alias]['url'] = 'http://' . $this->data[$this->alias]['url'];
			}
		}
		return true;
	}

/**
 * Logs a user in
 *
 * @param string $type type of login
 * @param array $credentials 
 * @return mixed user data if logged in, false otherwise
 */
	public function authsomeLogin($type, $credentials = array()) {
		switch ($type) {
			case 'guest':
				// You can return any non-null value here, if you don't
				// have a guest account, just return an empty array
				return array('guest' => 'guest');
			case 'credentials':
				// Don't even attempt to login an invalid email address
				if (!strstr($credentials['login'], '@')) {
					return false;
				}

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

		$data = $this->find('first', compact('conditions'));
		if (!$data) {
			return false;
		}

		$data[$this->alias]['loginType'] = $type;
		return $data;
	}

/**
 * Persists a user's login information
 *
 * @param array $data user data
 * @param string $duration Time in a date() compatible format
 * @return string login token to be parsed
 */
	public function authsomePersist($data, $duration) {
		$token = md5(uniqid(mt_rand(), true));
		$userId = $data[$this->alias][$this->primaryKey];

		$this->LoginToken->create(array(
			'user_id' => $userId,
			'token' => $token,
			'duration' => $duration,
			'expires' => date('Y-m-d H:i:s', strtotime($duration)),
		));
		$this->LoginToken->save();

		return "${token}:${userId}";
	}

/**
 * Changes the current user's activation key
 *
 * @param int $id record's primaryKey
 * @return mixed False on failure, new activation key otherwise
 * @author Jose Diaz-Gonzalez
 */
	public function changeActivationKey($id) {
		$activationKey = md5(uniqid());
		$data = array(
			$this->alias => array(
				$this->primaryKey => $id,
				'activation_key'  => $activationKey,
			),
		);

		if (!$this->save($data, array('callbacks' => false))) return false;
		return $activationKey;
	}

/**
 * Change password method
 *
 * @param array $data Data array containing old and new password
 * @return boolean True on success, false otherwise
 */
	public function changePassword($data) {
		if (!$data || !isset($data[$this->alias])) return false;

		$data = array($this->alias => array(
			'password' => $data[$this->alias]['password'],
			'new_password' => $data[$this->alias]['new_password'],
			'new_password_confirm' => $data[$this->alias]['new_password_confirm']
		));

		if ($data[$this->alias]['new_password'] != $data[$this->alias]['new_password_confirm']) {
			return false;
		}

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
			'fields' => array($this->primaryKey)
		));

		if (!$user) return false;
		return $this->save($data, array('fieldList' => array('id', 'password')));
	}

/**
 * Enqueues a forgotPassword job
 *
 * @param array $data User data
 * @return mixed True when job enqueued, false otherwise
 */
	public function forgotPassword($data) {
		if (empty($data[$this->alias]['email'])) return false;
		$data = $this->find('forgotpassword', $data[$this->alias]['email']);

		return $this->enqueue(new ForgotPasswordJob($data[$this->alias], $_SERVER['REMOTE_ADDR']));
	}

/**
 * Resets a password and the activation key for a given user
 *
 * @param array $data 
 * @param array $params Array containing the name of a user and their activation key
 * @return boolean True on success, false otherwise
 */
	public function resetPassword($data, $params) {
		if (empty($data[$this->alias]['password'])) {
			return false;
		}

		$maintainer = $this->find('resetpassword', $params);
		if (!isset($maintainer)) {
			return false;
		}

		$data = array($this->alias => array(
			$this->primaryKey   => $maintainer[$this->alias][$this->primaryKey],
			'password'		  => Authsome::hash($data[$this->alias]['password']),
			'activation_key'	=> md5(uniqid())
		));
		return $this->save($data, array('fieldList' => array('id', 'password', 'activation_key')));
	}

	public function seoView($maintainer) {
		if ($maintainer['Maintainer']['name']) {
			$name = $maintainer['Maintainer']['name'];
		} else {
			$name = $maintainer['Maintainer']['username'];
		}

		$title = array();
		$title[] = Sanitize::clean($name);
		$title[] = 'CakePHP Package Maintainer';
		$title[] = 'CakePackages';
		$title = implode(' | ', $title);

		$description = Sanitize::clean($name) . ' - CakePHP Package on CakePackages';

		$keywords = array();
		if (!empty($maintainer['Package'])) {
			$keywords = array_slice(Set::classicExtract($maintainer, 'Package.{n}.name'), 0, 5);
		}
		$keywords[] = 'cakephp package';
		$keywords[] = 'cakephp';
		$keywords = implode(' | ', $keywords);

		return array($title, $description, $keywords);
	}
}