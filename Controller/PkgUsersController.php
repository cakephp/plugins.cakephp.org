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

App::uses('UsersController', 'Users.Controller')

/**
 * Packages app specific User controller
 *
 * @package cakepackages.controller
 * @subpackage cakepackages.controller
 */
class PkgUsersController extends UsersController {

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
			'actions' => array('register')
		)
	);

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

		parent::beforeFilter();
		$this->User = ClassRegistry::init('PkgUser');
		$this->Auth->allow('view');
		$this->Auth->deny('profile', 'rate');
		if ($this->request->action == 'view' || $this->request->action == 'profile') {
			array_push($this->helpers, 'Timezone', 'Time');
		}
	}

/**
 * Displays the current user public profile
 *
 * @return void
 * @see PkgUsersController::view
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
			$user = $this->User->view($slug);
			$alreadyRated = $this->User->Profile->isRatedBy($user['Profile']['id'], $this->Auth->user('id'));
			$this->set(compact('user', 'alreadyRated'));
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage());
			$this->redirect('/');
		}

		$uploaded = $this->User->Video->find('count', array('conditions' => array('Video.user_id' => $user['PkgUser']['id'])));
		$name = $this->viewVars['user']['PkgUser']['username'];
		$this->set('uploaded', $uploaded);
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
				$this->Session->setFlash(__d('users', 'Profile saved.'));
			} else {
				$this->Session->setFlash(__d('users', 'Could not save your profile.'));
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
 * Check if the view file exist on app views, otherwise use the view file from users plugin
 *
 * @param $action
 * @param $layout
 * @param $file
 * @return string
 */
	public function render($action = null, $layout = null, $file = null) {
		if (is_null($action)) {
			$action = $this->request->action;
		}

		$theme = null;
		if (!empty($this->theme)) {
			$theme = 'themed' . DS . $this->theme . DS;
		}

		if ($action !== false) {
			$file = APP . 'View' . DS . $theme . $this->viewPath . DS . $action . '.ctp';
			if (!file_exists($file)) {
				$file = APP . 'View' . DS . $this->viewPath . DS . $action . '.ctp';

				if (!file_exists($file)) {
					$file = App::pluginPath('users') . 'views' . DS . 'users' . DS . $action . '.ctp';
				}
			}
		}

		return parent::render($action, $layout, $file);
	}

/**
 * Common login action
 *
 * @return void
 */
	public function login() {
		if ($this->RequestHandler->isAjax() && $this->RequestHandler->accepts('json')) {
			$this->Auth->login($this->request->data);
			$this->layout = 'default';
			$this->RequestHandler->renderAs($this, 'json');
			return;
		}
		if ($this->Auth->user()) {
			$this->User->id = $this->Auth->user('id');
			$this->User->saveField('last_login', date('Y-m-d H:i:s'));

			if ($this->request->here == $this->Auth->loginRedirect) {
				$this->Auth->loginRedirect = '/';
			}

			$this->Session->setFlash(sprintf(__("%s you have successfully logged in"), $this->Auth->user('username')));
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
		$this->set('title_for_layout', __('Login'));
	}


/**
 * Retrieves a list of active users on the system
 *
 * @return void
 */
	public function index() {
		$searchTerm = '';
		$this->Prg->commonProcess($this->modelClass, $this->modelClass, 'index', false);

		if (!empty($this->request->params['named']['search'])) {
			$searchTerm = $this->request->params['named']['search'];
			$this->request->data[$this->modelClass]['search'] = $searchTerm;
		}

		$this->paginate = array(
			'search',
			'limit' => 12,
			'order' => $this->modelClass . '.username ASC',
			'contain' => array('Profile'),
			'by' => 'any',
			'search' => $searchTerm,
			'conditions' => array(
				$this->modelClass . '.username !=' => 'Admin',
				$this->modelClass . '.active' => 1,
				$this->modelClass . '.email_authenticated' => 1
			)
		);

		if (!empty($this->request->params['named']['sortby'])) {
			$sort = $this->request->params['named']['sortby'];
			if (isset($this->User->findMethods[$sort])) {
				$this->paginate = Set::merge($this->paginate, $this->User->findMethods[$sort]);
			}
		}

		$this->set('users', $this->paginate($this->modelClass));
		$this->set('searchTerm', $searchTerm);

		if (!isset($this->request->params['named']['sort'])) {
			$this->request->params['named']['sort'] = 'username';
		}
		$this->set('title_for_layout', __('List of active members'));
	}

}