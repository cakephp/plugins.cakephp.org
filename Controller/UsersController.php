<?php
/**
 * Copyright 2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Packages app specific User controller
 *
 * @package cakepackages.controller
 * @subpackage cakepackages.controller
 */
class UsersController extends AppController {

/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array(
		'Html',
		'Form',
		'Session',
		'Time',
		'Text',
		'Utils.Gravatar' => array(
			'default' => 'monsterid'
		),
	);

/**
 * Components
 *
 * @var array
 */
	public $components = array(
		'Auth',
		'Session',
		'Email',
		'Cookie',
		'Search.Prg',
		'Recaptcha.Recaptcha' => array(
			'actions' => array('register'),
		),
	);


/**
 * Constructor
 *
 * @param CakeRequest $request Request object for this controller. Can be null for testing,
 *  but expect that features that use the request parameters will not work.
 * @param CakeResponse $response Response object for this controller.
 */
	public function __construct($request, $response) {
		$this->_setupComponents();
		$this->_setupHelpers();
		parent::__construct($request, $response);
	}

/**
 * Setup components based on plugin availability
 *
 * @return void
 * @link https://github.com/CakeDC/search
 */	
	protected function _setupComponents() {
		if (App::import('Component', 'Search.Prg')) {
			$this->components[] = 'Search.Prg';
		}
	}

/**
 * Setup helpers based on plugin availability
 *
 * @return void
 */	
	protected function _setupHelpers() {
		if (App::import('Helper', 'Goodies.Gravatar')) {
			$this->helpers[] = 'Goodies.Gravatar';
		}
	}

/**
 * beforeFilter callback
 *
 * @var array
 */
	public function beforeFilter() {
		if ($this->request->action == 'login') {
			if (!empty($this->request->data)) {
				$this->tmpData = $this->request->data;
			}
		}

		$this->set('model', $this->modelClass);
		parent::beforeFilter();
		if ($this->request->action == 'view' || $this->request->action == 'profile') {
			array_push($this->helpers, 'Timezone', 'Time');
		}
	}

/**
 * Common login action
 *
 * @return void
 */
	public function login() {
		if ($this->request->is('post') && $this->Auth->login()) {
			$this->User->loggedIn();

			if ($this->request->here == $this->Auth->loginRedirect) {
				$this->Auth->loginRedirect = '/';
			}

			$this->Session->setFlash(
				sprintf(__("%s, you have successfully logged in"), $this->Auth->user('username')),
				'flash/success'
			);
			if (!empty($this->request->data)) {
				$data = $this->request->data[$this->modelClass];
				$this->_setCookie();
			}

			if (empty($data['return_to'])) {
				$redirect = $this->Session->read('Auth.redirect');
				$data['return_to'] = $redirect ? $redirect : $this->Auth->redirect();
			}

			$this->redirect($data['return_to']);
		}

		if (isset($this->request->params['url']['return_to'])) {
			$this->set('return_to', urldecode($this->request->params['url']['return_to']));
		} else {
			$this->set('return_to', false);
		}
	}

/**
 * Common logout action
 *
 * @return void
 */
	public function logout() {
		$user = $this->Auth->user();
		$this->Session->destroy();
		$this->Cookie->destroy();
		$this->Session->setFlash(sprintf(
			__('%s you have successfully logged out'),
			$user[$this->{$this->modelClass}->displayField]
		), 'flash/info');
		$this->redirect($this->Auth->logout());
	}

/**
 * User register action
 *
 * @return void
 */
	public function register() {
		if (!empty($this->request->data)) {
			if ($this->User->register($this->request->data)) {
				$this->Session->setFlash(__('Your account has been created. You should receive an e-mail shortly to authenticate your account. Once validated you will be able to login.'), 'flash/success');
				$this->redirect(array('action' => 'login'));
			} else {
				unset($this->request->data[$this->modelClass]['password']);
				unset($this->request->data[$this->modelClass]['temppassword']);
				$this->Session->setFlash(
					__('Your account could not be created. Please, try again.'),
					'flash/error',
					array('class' => 'message warning')
				);
			}
		}
	}

/**
 * Reset Password Action
 *
 * Handles the trigger of the reset, also takes the token, validates it and let the user enter
 * a new password.
 *
 * @param string $token Token
 * @param string $user User Data
 * @return void
 */
	public function forgot_password() {
		if (!empty($this->data)) {
			try {
				if ($this->User->forgotPassword($this->data)) {
					$this->Session->setFlash(__('An email has been sent with instructions for resetting your password'), 'flash/info');
					$this->redirect(array('controller' => 'users', 'action' => 'login'));
				} else {
					$this->Session->setFlash(__('Error resetting password'), 'flash/error');
				}

			} catch (Exception $e) {
				$this->_flashAndRedirect($e->getMessage(), array('controller' => 'users', 'action' => 'forgot_password'));
			}
		}
	}

/**
 * Reset Password Action
 *
 * Handles the trigger of the reset, also takes the token, validates it and let the user enter
 * a new password.
 *
 * @param string $token Token
 * @param string $user User Data
 * @return void
 */
	public function reset_password($token = null) {
		try {
			$user = $this->User->find('resetPassword', $token);
		} catch (Exception $e) {
			$this->Session->setFlash(__('Invalid password reset token, try again.'), 'flash/error');
			$this->redirect(array('action' => 'forgot_password'));
		}

		if (!empty($this->request->data)) {
			if ($this->User->resetPassword(Set::merge($user, $this->request->data))) {
				$this->Session->setFlash(__('Password changed, you can now login with your new password.'), 'flash/success');
				$this->redirect($this->Auth->loginAction);
			} else {
				$this->Session->setFlash(__('Unable to update password, please try again.'), 'flash/error');
			}
		}

		$this->set('token', $token);
	}

/**
 * Confirm email action
 *
 * @param string $type Type, deprecated, will be removed. Its just still there for a smooth transistion.
 * @param string $token Token
 * @return void
 */
	public function verify($token = null) {
		try {
			$this->User->isValidEmail($token);
			$this->Session->setFlash(__('Your e-mail has been validated!'), 'flash/success');
			return $this->redirect(array('action' => 'login'));
		} catch (RuntimeException $e) {
			$this->Session->setFlash($e->getMessage(), 'flash/error');
			return $this->redirect('/');
		}
	}

/**
 * Displays the current user public profile
 *
 * @return void
 * @see UsersController::view
 */
	public function profile() {
		$this->setAction('view', $this->Auth->user('slug'));
		$this->set('title_for_layout', __('My Profile'));
	}

/**
 * Shows a users profile
 *
 * @param string $slug User Slug
 * @return void
 */
	public function view($slug = null) {
		try {
			$user = $this->User->find('view', $slug);
			$this->set(compact('user'));
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
			$this->redirect('/');
		}

		$name = $user[$this->modelClass]['username'];
		$this->set('title_for_layout', sprintf(__('%s\'s profile page'), $name));
	}

/**
 * Edit
 *
 * @param string $id User ID
 * @return void
 */
	public function edit() {
		if (!empty($this->request->data)) {
			if ($this->User->Detail->saveSection($this->Auth->user('id'), $this->request->data, 'User')) {
				$this->Session->setFlash(__('Profile saved.'), 'flash/success');
			} else {
				$this->Session->setFlash(__('Could not save your profile.'), 'flash/error');
			}
		} else {
			$this->request->data = $this->User->find('first', array(
				'conditions' => array($this->User->alias . '.id' => $this->Auth->user('id')),
				'contain' => array('Detail')
			));
		}
		$this->set('title_for_layout', __('Edit account information'));
		$this->_setLanguages();
	}

/**
 * Sets the cookie to remember the user
 *
 * @param array Cookie component properties as array, like array('domain' => 'yourdomain.com')
 * @param string Cookie data keyname for the userdata, its default is "User". This is set to User and NOT using the model alias to make sure it works with different apps with different user models across different (sub)domains.
 * @return void
 * @link http://book.cakephp.org/2.0/en/core-libraries/components/cookie.html
 */
	protected function _setCookie($options = array(), $cookieKey = 'User') {
		if (empty($this->request->data[$this->modelClass]['remember_me'])) {
			$this->Cookie->delete($cookieKey);
		} else {
			$validProperties = array('domain', 'key', 'name', 'path', 'secure', 'time');
			$defaults = array(
				'name' => 'rememberMe');

			$options = array_merge($defaults, $options);
			foreach ($options as $key => $value) {
				if (in_array($key, $validProperties)) {
					$this->Cookie->{$key} = $value;
				}
			}

			$cookieData = array(
				'username' => $this->request->data[$this->modelClass]['username'],
				'password' => $this->request->data[$this->modelClass]['password']);
			$this->Cookie->write($cookieKey, $cookieData, true, '1 Month');
		}
		unset($this->request->data[$this->modelClass]['remember_me']);
	}

	public function _seoRegister() {
		$this->Sham->setMeta('title', 'Become a Member');
		$this->Sham->setMeta('description', 'User registration');
	}

/**
 * Sets some meta headers for the response
 *
 * @return void
 */
	public function _seoFallback() {
		if (!$this->Sham->getMeta('title')) {
			$this->Sham->setMeta('title', Inflector::humanize($this->request->params['action']) . ' | CakePackages');
		}

		parent::_seoFallback();
	}

}