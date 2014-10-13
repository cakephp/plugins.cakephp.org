<?php
App::uses('Security', 'Utility');

class User extends AppModel {

/**
 * List of behaviors to load when the model object is initialized. Settings can be
 * passed to behaviors by using the behavior name as index.
 *
 * @var array
 */
	public $actsAs = array(
		'Ratings.Ratable',
		'Utils.Sluggable' => array(
			'label' => 'username',
			'method' => 'multibyteSlug'
		),
	);

/**
 * List of valid finder method options, supplied as the first parameter to find().
 *
 * @var array
 */
	public $findMethods = array(
		'forgotpassword' => true,
		'resetPassword' => true,
		'unverifiedToken' => true,
		'validateToken' => true,
		'view' => true,
	);

/**
 * Detailed list of hasMany associations.
 *
 * @var array
 * @link http://book.cakephp.org/2.0/en/models/associations-linking-models-together.html#hasmany
 */
	public $hasMany = array(
		'UserDetail',
	);

/**
 * Detailed list of hasOne associations.
 *
 * @var array
 * @link http://book.cakephp.org/2.0/en/models/associations-linking-models-together.html#hasone
 */
	public $hasOne = array(
		'Maintainer',
	);

/**
 * Validation parameters
 *
 * @var array
 */
	public $validate = array(
		'username' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'required' => true, 'allowEmpty' => false,
				'message' => 'Please enter a username.'),
			'alpha' => array(
				'rule' => array('alphaNumeric'),
				'message' => 'The username must be alphanumeric.'),
			'unique_username' => array(
				'rule' => array('isUnique', 'username'),
				'message' => 'This username is already in use.'),
			'username_min' => array(
				'rule' => array('minLength', '3'),
				'message' => 'The username must have at least 3 characters.',
			),
		),
		'email' => array(
			'isValid' => array(
				'rule' => 'email',
				'required' => true,
				'message' => 'Please enter a valid email address.'),
			'isUnique' => array(
				'rule' => array('isUnique', 'email'),
				'message' => 'This email is already in use.',
			),
		),
		'password' => array(
			'to_short' => array(
				'rule' => array('minLength', '6'),
				'message' => 'The password must have at least 6 characters.'),
			'required' => array(
				'rule' => 'notEmpty',
				'message' => 'Please enter a password.',
			),
		),
		'temppassword' => array(
			'rule' => 'validatePassword',
			'message' => 'The passwords are not equal, please try again.'),
		'tos' => array(
			'rule' => array('custom', '/[1]/'),
			'message' => 'You must agree to the terms of use.',
		),
	);

/**
 * Constructor
 *
 * @param mixed $id Model ID
 * @param string $table Table name
 * @param string $ds Datasource
 */
	public function __construct($id = false, $table = null, $ds = null) {
		$this->_setupValidation();
		parent::__construct($id, $table, $ds);
		$this->findMethods['name'] = array(
			'order' => array('User.username' => 'asc')
		);
	}

/**
 * Setup validation rules
 *
 * @return void
 */
	protected function _setupValidation() {
		$this->validatePasswordChange = array(
			'new_password' => $this->validate['password'],
			'confirm_password' => array(
				'required' => array(
					'rule' => array('compareFields', 'new_password', 'confirm_password'),
					'required' => true,
					'message' => __d('users', 'The passwords are not equal.')
				)
			),
			'old_password' => array(
				'to_short' => array(
					'rule' => 'validateOldPassword',
					'required' => true,
					'message' => __d('users', 'Invalid password.')
				)
			)
		);
		$this->validateResetPassword = array(
			'new_password' => $this->validate['password'],
			'confirm_password' => array(
				'required' => array(
					'rule' => array('compareFields', 'new_password', 'confirm_password'),
					'message' => __('The passwords are not equal.')
				)
			)
		);
	}

/**
 * Finds a user by email for reseting their password
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @return mixed array of results or false if none found
 * @return array
 */
	protected function _findForgotpassword($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Invalid email address'));
			}

			$query['contain'] = false;
			$query['conditions'] = array("{$this->alias}.email" => $query[0]);
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new NotFoundException(__('User does not exist'));
			}
			return $results[0];
		}
	}

	protected function _findResetPassword($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query['token'])) {
				throw new InvalidArgumentException(__('Invalid email address'));
			}

			$query['contain'] = false;
			$query['conditions'] = array(
				$this->alias . '.active' => 1,
				$this->alias . '.password_token' => $query['token'],
				$this->alias . '.email_token_expires >=' => date('Y-m-d H:i:s')
			);

			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new NotFoundException(__('User does not exist'));
			}
			return $results[0];
		}
	}

	protected function _findUnverifiedToken($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query['token'])) {
				throw new InvalidArgumentException(__('Invalid token'));
			}

			$query['conditions'] = array(
				$this->alias . '.email_authenticated' => 0,
				$this->alias . '.email_token' => $query['token'],
			);
			$query['fields'] = array(
				'id', 'email', 'email_token_expires', 'role'
			);
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new NotFoundException(__('User does not exist'));
			}
			return $results[0];
		}
	}

	protected function _findValidateToken($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query['token'])) {
				throw new InvalidArgumentException(__('Invalid token'));
			}

			$query['conditions'] = array(
				"{$this->alias}.email_token" => $query['token'],
			);
			$query['fields'] = array(
				'id', 'email', 'email_token_expires', 'role'
			);
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new NotFoundException(__('User does not exist'));
			}
			return $results[0];
		}
	}

	protected function _findView($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query['slug'])) {
				throw new InvalidArgumentException(__('Invalid user'));
			}

			$query['conditions'] = array(
				"{$this->alias}.slug" => $query['slug'],
			);
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new NotFoundException(__('User does not exist'));
			}
			return $results[0];
		}
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
			if (isset($row['UserDetail']) && (is_array($row))) {
				$detail = $this->UserDetail->getSection($row[$this->alias]['id'], $this->alias);
				$row['UserDetail'] = !empty($detail['User']) ? $detail['User'] : array();
				$row[$this->alias] = array_merge($row[$this->alias], $row['UserDetail']);

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
 * Custom validation method to ensure that the two entered passwords match
 *
 * @param string $password Password
 * @return boolean Success
 */
	public function validatePassword($password = null) {
		if (!isset($this->data[$this->alias]['passwd']) || !isset($password['temppassword'])) {
			return false;
		}
		if (empty($password['temppassword'])) {
			return false;
		}
		if (!($this->data[$this->alias]['passwd'] === $password['temppassword'])) {
			return false;
		}
		return true;
	}

/**
 * Compares the email confirmation
 *
 * @param array $email Email data
 * @return boolean
 */
	public function validateEmail($email = null) {
		if ((isset($this->data[$this->alias]['email']) && isset($email['confirm_email']))
			&& !empty($email['confirm_email'])
			&& (strtolower($this->data[$this->alias]['email']) === strtolower($email['confirm_email']))) {
				return true;
		}
		return false;
	}

/**
 * Validation method to compare two fields
 *
 * @param mixed $field1 Array or string, if array the first key is used as fieldname
 * @param string $field2 Second fieldname
 * @return boolean True on success
 */
	public function compareFields($field1, $field2) {
		if (is_array($field1)) {
			$field1 = key($field1);
		}
		if (isset($this->data[$this->alias][$field1]) && isset($this->data[$this->alias][$field2]) &&
			$this->data[$this->alias][$field1] == $this->data[$this->alias][$field2]) {
			return true;
		}
		return false;
	}

/**
 * Changes the current user's activation key
 *
 * @param int $id record's primaryKey
 * @return mixed False on failure, new activation key otherwise
 */
	public function changeActivationKey($user) {
		$sixtyMins = time() + 43000;
		$token = $this->_generateToken();
		$user['password_token'] = $token;
		$user['email_token_expires'] = date('Y-m-d H:i:s', $sixtyMins);
		if (!$this->save(array('User' => $user), false)) {
			return false;
		}

		return $token;
	}

/**
 * Enqueues a forgotPassword job
 *
 * @param array $data User data
 * @return boolean True when job enqueued, false otherwise
 */
	public function forgotPassword($data) {
		if (empty($data[$this->alias]['email'])) {
			$this->invalidate('email', 'Invalid Email Address');
			return false;
		}

		try {
			$data = $this->find('forgotpassword', $data[$this->alias]['email']);
		} catch (Exception $e) {
			$this->invalidate('email', 'Invalid Email Address');
			return false;
		}

		if ($data[$this->alias]['email_authenticated'] == 0) {
			$this->invalidate('email', __('This Email Address exists but was never validated.'));
			return false;
		}
		return $this->enqueue('UserForgotPasswordJob', array(array(
			'user' => $data[$this->alias],
			'ipaddress' => $_SERVER['REMOTE_ADDR'],
		)));
	}

/**
 * Validates the user token
 *
 * @deprecated See isValidEmail()
 * @param string $token Token
 * @param boolean $reset Reset boolean
 * @param boolean $now time() value
 * @return mixed false or user data
 */
	public function isValidToken($token = null, $reset = false, $now = null) {
		if (!$now) {
			$now = time();
		}

		$data = false;
		try {
			$match = $this->find('validateToken', compact('token'));
		} catch (Exception $e) {
			return $data;
		}

		$expires = strtotime($match[$this->alias]['email_token_expires']);
		if ($expires > $now) {
			$data[$this->alias]['id'] = $match[$this->alias]['id'];
			$data[$this->alias]['email'] = $match[$this->alias]['email'];
			$data[$this->alias]['email_authenticated'] = '1';
			$data[$this->alias]['role'] = $match[$this->alias]['role'];

			if ($reset === true) {
				$newPassword = $this->_generatePassword();
				$data[$this->alias]['passwd'] = Security::hash($newPassword, null, true);
				$data[$this->alias]['new_password'] = $newPassword;
				$data[$this->alias]['password_token'] = null;
			}

			$data[$this->alias]['email_token'] = null;
			$data[$this->alias]['email_token_expires'] = null;
		}

		return $data;
	}

/**
 * Updates last_login
 *
 * @return boolean result of Model::saveField() operation
 */
	public function loggedIn() {
		$this->id = AuthComponent::user('id');
		return $this->saveField('last_login', date('Y-m-d H:i:s'));
	}

/**
 * Registers a new user
 *
 * Options:
 * - bool emailVerification : Default is true, generates the token for email verification
 * - bool removeExpiredRegistrations : Default is true, removes expired registrations to do cleanup when no cron is configured for that
 * - bool returnData : Default is true, if false the method returns true/false the data is always available through $this->User->data
 *
 * @param array $postData Post data from controller
 * @param mixed should be array now but can be boolean for emailVerification because of backward compatibility
 * @return mixed
 */
	public function register($postData = array(), $options = array()) {
		if (is_bool($options)) {
			$options = array('emailVerification' => $options);
		}

		$options = array_merge(array(
			'emailVerification' => true,
			'removeExpiredRegistrations' => true,
			'returnData' => true,
		), $options);

		$postData = $this->_beforeRegistration($postData, $options['emailVerification']);

		if ($options['removeExpiredRegistrations']) {
			$this->_removeExpiredRegistrations();
		}

		$this->set($postData);
		if ($this->validates()) {
			$postData[$this->alias]['passwd'] = Security::hash($postData[$this->alias]['passwd'], 'sha1', true);
			$this->create();
			$this->data = $this->save($postData, false);
			$this->data[$this->alias]['id'] = $this->id;
			$this->_sendVerificationEmail($this->data);
			if ($options['returnData']) {
				return $this->data;
			}
			return true;
		}
		return false;
	}

/**
 * Resets the password
 *
 * @param array $postData Post data from controller
 * @return boolean True on success
 */
	public function resetPassword($postData = array()) {
		$result = false;

		$tmp = $this->validate;
		$this->validate = $this->validateResetPassword;

		$this->set($postData);
		if ($this->validates()) {
			$this->data[$this->alias]['passwd'] = Security::hash($this->data[$this->alias]['new_password'], null, true);
			$this->data[$this->alias]['password_token'] = null;
			$result = $this->save($this->data, array(
				'validate' => false,
				'callbacks' => false
			));
		}

		$this->validate = $tmp;
		return $result;
	}

/**
 * Verifies a users email by a token that was sent to him via email and flags the user record as active
 *
 * @param string $token The token that wa sent to the user
 * @return array On success it returns the user data record
 */
	public function isValidEmail($token = null) {
		try {
			$user = $this->find('unverifiedToken', compact('token'));
		} catch (Exception $e) {
			throw new RuntimeException(__d('users', 'Invalid token, please check the email you were sent, and retry the verification link.'));
		}

		$expires = strtotime($user[$this->alias]['email_token_expires']);
		if ($expires < time()) {
			throw new RuntimeException(__d('users', 'The token has expired.'));
		}

		$user[$this->alias]['active'] = 1;
		$user[$this->alias]['email_authenticated'] = 1;
		$user[$this->alias]['email_token'] = null;
		$user[$this->alias]['email_token_expires'] = null;

		return $this->data = $this->save($user, array(
			'validate' => false,
			'callbacks' => false
		));
	}

/**
 * Optional data manipulation before the registration record is saved
 *
 * @param array post data array
 * @param boolean Use email generation, create token, default true
 * @return array
 */
	protected function _beforeRegistration($postData = array(), $useEmailVerification = true) {
		if ($useEmailVerification == true) {
			$postData[$this->alias]['email_token'] = $this->_generateToken();
			$postData[$this->alias]['email_token_expires'] = date('Y-m-d H:i:s', time() + 86400);
		} else {
			$postData[$this->alias]['email_authenticated'] = 1;
		}
		$postData[$this->alias]['active'] = 1;
		return $postData;
	}

/**
 * Generates a password
 *
 * @param int $length Password length
 * @return string
 */
	protected function _generatePassword($length = 10) {
		srand((double)microtime() * 1000000);
		$password = '';
		$vowels = array("a", "e", "i", "o", "u");
		$cons = array("b", "c", "d", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "u", "v", "w", "tr",
							"cr", "br", "fr", "th", "dr", "ch", "ph", "wr", "st", "sp", "sw", "pr", "sl", "cl");
		for ($i = 0; $i < $length; $i++) {
			$password .= $cons[mt_rand(0, 31)] . $vowels[mt_rand(0, 4)];
		}
		return substr($password, 0, $length);
	}

/**
 * Generate token used by the user registration system
 *
 * @param int $length Token Length
 * @return string
 */
	protected function _generateToken($length = 10) {
		$possible = '0123456789abcdefghijklmnopqrstuvwxyz';
		$token = "";
		$i = 0;

		while ($i < $length) {
			$char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);
			if (!stristr($token, $char)) {
				$token .= $char;
				$i++;
			}
		}
		return $token;
	}

/**
 * Removes all users from the user table that are outdated
 *
 * Override it as needed for your specific project
 *
 * @return boolean Result of Model::deleteAll() call
 */
	protected function _removeExpiredRegistrations() {
		return $this->deleteAll(array(
			$this->alias . '.email_authenticated' => 0,
			$this->alias . '.email_token_expires <' => date('Y-m-d H:i:s')
		));
	}

/**
 * Sends the verification email
 *
 * @param string $to Receiver email address
 * @param array $options EmailComponent options
 * @return boolean Success
 */
	protected function _sendVerificationEmail($userData) {
		return Resque::enqueue('default', 'UserVerificationEmailJob', array('work', compact('userData')));
	}

}
