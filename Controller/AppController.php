<?php
App::uses('Controller', 'Controller');

class AppController extends Controller {

/**
 * Array containing the names of components this controller uses. Component names
 * should not contain the "Component" portion of the classname.
 *
 * Example: `var $components = array('Session', 'RequestHandler', 'Acl');`
 *
 * @var array
 * @link http://book.cakephp.org/view/961/components-helpers-and-uses
 */
	public $components = array(
		'Auth',
		'Cookie',
		'RequestHandler',
		'Sanction.Permit' => array(
			'path' => 'Auth.User'
		),
		'Session',
		'Sham.Sham' => array(
			'autoRun' => false,
		),
	);

/**
 * An array containing the names of helpers this controller uses. The array elements should
 * not contain the "Helper" part of the classname.
 *
 * Example: `var $helpers = array('Html', 'Javascript', 'Time', 'Ajax');`
 *
 * @var mixed A single name as a string or a list of names as an array.
 * @link http://book.cakephp.org/view/961/components-helpers-and-uses
 */
	public $helpers = array(
		'AssetCompress.AssetCompress',
		'Form',
		'Html',
		'Js',
		'Resource',
		'Sanction.Clearance' => array(
			'path' => 'Auth.User'
		),
		'Session',
		'Sham.Sham',
		'Text',
		'Time',
		'Utils.Gravatar',
	);

/**
 * Sets the default redirection array
 *
 * @var array
 */
	public $redirectTo = array('action' => 'index');

/**
 * Used to set a max for the pagination limit
 *
 * @var int
 */
	public $paginationMaxLimit = 25;

/**
 * Blacklist some variables from webservice output
 *
 * @var array
 */
	public $_blacklistVars = array('_meta', '_bodyId', '_bodyClass');

/**
 * Whitelist of actions allowing ajax support
 *
 * @var string
 */
	protected $_ajax = array();

/**
 * Object constructor - Adds the Debugkit panel if in development mode
 *
 * @return void
 */
	public function __construct($request = null, $response = null) {
		if (Configure::read('debug')) {
			$this->components['DebugKit.Toolbar'] = array(
				'panels' => array('Sanction.Permit', 'Configure'
			));
		}

		if (Configure::read('Settings.theme')) {
			$this->theme = Configure::read('Settings.theme');
			$this->viewClass = 'Theme';
		}

		parent::__construct($request, $response);
	}

/**
 * Before filter callback
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->_setupTheme();
		$this->_setupAuth();
		$this->_beforeFilterAuth();

		if (!isset($this->request->params['prefix']) || $this->request->params['prefix'] != 'admin') {
			$this->Auth->allow();
		}

		// Enforces an absolute limit of 25
		if (isset($this->passedArgs['limit'])) {
			$this->passedArgs['limit'] = min(
				$this->paginationMaxLimit,
				$this->passedArgs['limit']
			);
		}

	}

/**
 * Setup Theme
 *
 * @return boolean True if theme set, false otherwise
 **/
	public function _setupTheme() {
		if (($theme = Configure::read('Config.theme')) === null) {
			return false;
		}

		$is_available = Cache::read('Theme.is_available');
		if (!$is_available) {
			$path = App::themePath($theme);
			$is_available = file_exists($path . 'README.textile') ? 'yes' : 'no';
			Cache::write('Theme.is_available', $is_available);
		}

		if ($is_available === 'yes') {
			$this->theme = $theme;
		}
		return $is_available === 'yes';
	}

/**
 * Setup Authentication
 *
 * @return void
 */
	protected function _setupAuth() {
		$this->Auth->authorize = array('Controller');
		$this->Auth->loginAction = array(
			'plugin' => null,
			'admin' => false,
			'controller' => 'users',
			'action' => 'login'
		);
		$this->Auth->loginRedirect = '/';
		$this->Auth->logoutRedirect = '/';
		$this->Auth->authenticate = array(
			'all' => array(
				'fields' => array('username' => 'email', 'password' => 'passwd'),
				'userModel' => 'User',
				'scope' => array(
					'User.email_authenticated' => 1,
					'User.active' => 1,
				),
			),
			'Form',
		);
	}

/**
 * beforeFilterAuth
 *
 * @return void
 */
	protected function _beforeFilterAuth() {
		$this->Cookie->domain = env('HTTP_BASE');
		$this->Cookie->name = 'rememberMe';
		$cookie = $this->Cookie->read('User');
		if (!empty($cookie) && !$this->Auth->user()) {
			$data['User'][$this->Auth->fields['username']] = $cookie[$this->Auth->fields['username']];
			$data['User'][$this->Auth->fields['password']] = $cookie[$this->Auth->fields['password']];
			$this->Auth->login($data);
		}
	}

/**
 * Dummy isAuthorized Auth callback
 *
 * Sanction.Permit handles permissions for us
 *
 * @return boolean true
 */
	public function isAuthorized() {
		return true;
	}

/**
 * Sets some meta headers for the response
 *
 * @return void
 */
	public function _seoFallback() {
		if ($this->request->params['controller'] == 'blog_posts') {
			if ($this->request->params['action'] == 'view') {
				$this->Sham->setMeta('title', $this->viewVars['blogPost']['BlogPost']['title'] . ' | Developer Blog | CakePackages');
				$this->Sham->setMeta('canonical', '/posts/' . $this->viewVars['blogPost']['BlogPost']['slug'] . '/');
			} else {
				$this->Sham->setMeta('title', 'Developer Blog | CakePackages');
				$this->Sham->setMeta('canonical', '/posts/');
			}
			$this->Sham->setMeta('description', 'CakePackages Developer Blog - Notes on the development and future of CakePackages');
		} elseif ($this->request->params['controller'] == 'pages') {
			$this->Sham->setMeta('title', $this->viewVars['title_for_layout'] . ' | CakePackages');
			$this->Sham->setMeta('canonical', '/' . $this->viewVars['page'] . '/');
		}

		if (!$this->Sham->getMeta('title')) {
			$this->Sham->setMeta('title', Inflector::humanize($this->request->params['controller']) . ' ' . $this->request->params['action'] . ' | CakePackages');
		}

		if (!$this->Sham->getMeta('description')) {
			$this->Sham->setMeta('description', 'CakePHP Package Index - Search for reusable, open source CakePHP plugins and applications, tutorials and code snippets on CakePackages');
		}

		if (!$this->Sham->getMeta('keywords')) {
			$this->Sham->setMeta('keywords', 'cakephp package, cakephp, plugins, php, open source code, tutorials');
		}
	}

/**
 * Convenience method for logging a user out of the application completely
 *
 * @param mixed $redirect If false, do not redirect, else redirect to specified action
 * @return void
 */
	protected function _logout($redirect = array('action' => 'login')) {
		$this->Auth->logout();

		if ($redirect) {
			$this->redirect($redirect);
		}
	}

/**
 * Redirect to some url if a given piece of information evaluates to false
 *
 * @param mixed $data Data to evaluate
 * @param mixed $message Message to use when redirecting
 * @return void
 */
	protected function _redirectUnless($data = null, $message = null) {
		if (empty($data)) {
			$redirectTo = array();
			$status = null;
			$exit = true;
			$element = 'flash/error';

			if (is_array($message)) {
				if (isset($message['redirectTo'])) $redirectTo = $message['redirectTo'];
				if (isset($message['status'])) $status = $message['status'];

				if (isset($message['exit'])) $exit = $message['exit'];
				if (isset($message['message'])) $message = $message['message'];
				if (isset($message['element'])) $element = $message['element'];
			}

			if ($message === null) {
				$message = __('Access Error');
			}

			if (is_array($redirectTo)) {
				$redirectTo = array_merge($this->redirectTo, $redirectTo);
			}

			if ($message !== false) {
				$this->Session->setFlash($message, $element);
			}

			$this->redirect($redirectTo, $status, $exit);
		}
	}

/**
 * Sets the currently logged in user as a view variable
 *
 * Also sets the body class and id
 *
 * @return void
 */
	public function beforeRender() {
		if (in_array($this->action, $this->_ajax) && $this->_is('ajax', 'post')) {
			$this->_respondAs('ajax');
		}

		$_bodyId = "{$this->request->params['controller']}";
		$_bodyClass = "{$this->request->params['controller']}-{$this->request->params['action']}";
		$this->set(compact('_bodyId', '_bodyClass'));
	}

	public function redirect($url, $status = null, $exit = true) {
		if (in_array($this->action, $this->_ajax) && $this->_is('ajax', 'post')) {
			$url = Router::url($url, true);
			if (!empty($status)) {
				$codes = $this->httpCodes();

				if (is_string($status)) {
					$codes = array_flip($codes);
				}

				if (isset($codes[$status])) {
					$code = $msg = $codes[$status];
					if (is_numeric($status)) {
						$code = $status;
					}
					if (is_string($status)) {
						$msg = $status;
					}
					$status = "HTTP/1.1 {$code} {$msg}";
				} else {
					$status = null;
				}
			}

			$this->set('_redirect', compact('url', 'status', 'exit'));
			return $this->_respondAs('ajax');
		}
		return parent::redirect($url, $status, $exit);
	}

	protected function _is() {
		$args = func_get_args();
		if (is_array($args[0])) {
			$args = $args[0];
		}

		foreach ($args as $arg) {
			if (!$this->RequestHandler->{'is' . ucfirst($arg)}()) {
				return false;
			}
		}
		return true;
	}

	protected function _respondAs($type) {
		if ($type == 'ajax') {
			$this->viewClass = 'Ajax';
			$_session = $this->Session->read('Message.flash');
			$this->Session->delete('Message.flash');
			if (empty($_session)) {
				return true;
			}

			list($_status, $_message) = array('success', '');
			if (!empty($_session['message'])) {
				$_message = $_session['message'];
			}

			if (!empty($_session['params']['class'])) {
				$_status = $_session['params']['class'];
			} elseif (strstr($_session['element'], '/') !== false) {
				$_status = substr(strstr($_session['element'], '/'), 1);
			}

			if (empty($_status)) {
				$_status = 'success';
			}

			$this->set(compact('_message', '_status'));
		}
	}

}