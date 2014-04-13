<?php
/**
 * AuthComponentTest file
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) Tests <https://trac.cakephp.org/wiki/Developement/TestSuite>
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 *  Licensed under The Open Group Test Suite License
 *  Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          https://trac.cakephp.org/wiki/Developement/TestSuite CakePHP(tm) Tests
 * @package       cake
 * @subpackage    cake.cake.tests.cases.libs.controller.components
 * @since         CakePHP(tm) v 1.2.0.5347
 * @license       http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
App::uses('PermitComponent', 'Sanction.Controller/Component');
App::uses('Session', 'Controller/Component');
App::uses('Controller', 'Controller');

/**
 * TestAuthComponent class
 *
 * @package       Sanction
 * @subpackage    Sanction.Test.Case.Controller.Component
 */
class TestPermitComponent extends PermitComponent {

/**
 * testStop property
 *
 * @var bool false
 */
	public $testStop = false;

/**
 * Sets default login state
 *
 * @var bool true
 */
	protected $_loggedIn = true;

/**
 * stop method
 *
 * @return void
 */
	protected function _stop($status = 0) {
		$this->testStop = true;
	}

}

/**
 * PermitTestController class
 *
 * @package       Sanction
 * @subpackage    Sanction.Test.Case.Controller.Component
 */
class PermitTestController extends Controller {

/**
 * name property
 *
 * @var string 'AuthTest'
 */
	public $name = 'AuthTest';

/**
 * uses property
 *
 * @var array
 */
	public $uses = array();

/**
 * components property
 *
 * @var array
 */
	public $components = array(
		'Sanction.Permit' => array(
			'path' => 'MockAuthTest',
			'check' => 'id',
			'isTest' => true,
		),
		'Session'
	);

/**
 * testUrl property
 *
 * @var mixed null
 */
	public $testUrl = null;

/**
 * construct method
 *
 * @return void
 */
	public function __construct($request, $response) {
		$request->addParams(Router::parse('/permit_tests'));
		$request->here = '/permit_tests';
		$request->webroot = '/';
		Router::setRequestInfo($request);
		parent::__construct($request, $response);
	}

/**
 * beforeFilter method
 *
 * @return void
 */
	public function beforeFilter() {
	}

/**
 * login method
 *
 * @return void
 */
	public function login() {
	}

/**
 * admin_login method
 *
 * @return void
 */
	public function admin_login() {
	}

/**
 * logout method
 *
 * @return void
 */
	public function logout() {
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		echo "add";
	}

/**
 * add method
 *
 * @return void
 */
	public function camelCase() {
		echo "camelCase";
	}

/**
 * redirect method
 *
 * @param mixed $url
 * @param mixed $status
 * @param mixed $exit
 * @return void
 */
	public function redirect($url, $status = null, $exit = true) {
		$this->testUrl = Router::url($url);
		return false;
	}

/**
 * Mock delete method
 *
 * @param mixed $url
 * @param mixed $status
 * @param mixed $exit
 * @return void
 */
	public function delete($id = null) {
		echo 'delete';
	}

}

/**
 * PermitTest class
 *
 * @package       Sanction
 * @subpackage    Sanction.Test.Case.Controller.Component
 */
class PermitTest extends CakeTestCase {

/**
 * name property
 *
 * @var string 'Auth'
 */
	public $name = 'Permit';

/**
 * initialized property
 *
 * @var bool false
 */
	public $initialized = false;

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->_server = $_SERVER;
		$this->_env = $_ENV;

		Configure::write('Security.salt', 'YJfIxfs2guVoUubWDYhG93b0qyJfIxfs2guwvniR2G0FgaC9mi');
		Configure::write('Security.cipherSeed', 770011223369876);

		$request = new CakeRequest(null, false);
		$request->params = array(
			'pass' => array(),
			'named' => array(),
			'plugin' => '', 'controller' => 'permit_tests',
			'action' => 'index'
		);
		$this->Controller = new PermitTestController($request, $this->getMock('CakeResponse'));

		$collection = new ComponentCollection();
		$collection->init($this->Controller);
		$this->Permit = new TestPermitComponent($collection, array(
			'path' => 'MockAuthTest',
			'check' => 'id',
			'isTest' => true,
		));
		$this->Permit->request = $request;
		$this->Permit->response = $this->getMock('CakeResponse');
		$this->Permit->routes = array();
		$this->Permit->executed = null;
		$this->Controller->Permit = $this->Permit;
		$this->Controller->Components->init($this->Controller);
		$this->Controller->Permit = $this->Permit;

		$this->Controller->Session->delete('Message.auth');
		$this->Controller->Session->write('MockAuthTest', array(
			'User' => array(
				'id' => 'user-logged-in',
				'email' => 'loggedin@domain.com',
				'group' => 'member',
				),
			'Role' => array(
				'id' => 'single-1',
				'name' => 'singleAssociation',
				'description' => 'hasOne or belongsTo association',
				),
			'Group' => array(
				0 => array(
					'id' => 'habtm-1',
					'name' => 'admin',
					'description' => 'HABTM association',
					),
				1 => array(
					'id' => 'habtm-2',
					'name' => 'editors',
					'description' => 'HABTM association',
					),
				),
			)
		);

		$this->initialized = true;
		Router::reload();
		Router::connect('/:controller/:action/*');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		$_SERVER = $this->_server;
		$_ENV = $this->_env;

		$this->Permit->Session->delete('MockAuthTest');
		$this->Permit->Session->delete('Message.auth');
		unset($this->Controller, $this->Permit);
	}

	public function protectedMethodCall($obj, $name, array $args) {
		$class = new \ReflectionClass($obj);
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method->invokeArgs($obj, $args);
	}

	public function testConstruct() {
		$collection = new ComponentCollection();
		$collection->init($this->Controller);
		$testPermit = new TestPermitComponent($collection, array(
			'path' => 'MockAuthTest',
			'check' => 'id',
			'isTest' => false,
			'permit_include_path' => dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'Config' . DS . 'permit.php'
		));
	}

	public function testConstructException() {
		$collection = new ComponentCollection();
		$collection->init($this->Controller);

		$this->setExpectedException('PermitException');
		$testPermit = new TestPermitComponent($collection, array(
			'path' => 'MockAuthTest',
			'check' => 'id',
			'isTest' => false,
			'permit_include_path' => dirname(dirname(dirname(dirname(__FILE__)))) . DS . 'Config' . DS . 'permit_exception.php'
		));
	}

	public function testConstructionMissing() {
		$collection = new ComponentCollection();
		$collection->init($this->Controller);

		$this->setExpectedException('PermitException');
		$testPermit = new TestPermitComponent($collection, array(
			'path' => 'MockAuthTest',
			'check' => 'id',
			'isTest' => false,
			'permit_include_path' => dirname(__FILE__) . DS . 'Config' . DS . 'permit.php'
		));
	}

	public function testSingleParse() {
		$testRoute = array();
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));

		$testRoute = array('controller' => 'permit_tests');
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));

		$testRoute = array('controller' => 'permit_tests', 'action' => 'index');
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));

		$testRoute = array('plugin' => null, 'controller' => 'permit_tests', 'action' => 'index');
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));

		$testRoute = array('controller' => 'permit_tests', 'action' => 'add');
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));

		$testRoute = array('controller' => 'users', 'action' => 'index');
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));
	}

	public function testSingleStringParse() {
		$testRoute = array();
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));

		$testRoute = '/permit_tests';
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));

		$testRoute = '/permit_tests/index';
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));

		$testRoute = '/permit_tests/add';
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));

		$testRoute = '/users/index';
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));
	}

	public function testMultipleParse() {
		$testRoute = array('controller' => 'permit_tests', 'action' => array('index'));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));

		$testRoute = array('controller' => 'permit_tests', 'action' => array('index', 'add'));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));

		$testRoute = array('controller' => array('permit_tests', 'users'), 'action' => array('index', 'add'));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));

		$testRoute = array(
			'plugin' => array(null, 'blog'),
			'controller' => array('permit_tests', 'users'),
			'action' => array('index', 'add')
		);
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));

		$testRoute = array('controller' => 'permit_tests', 'action' => array('add', 'edit', 'delete'));
		$result = $this->protectedMethodCall($this->Permit, '_parse', array($testRoute));
		$this->assertFalse($result);
	}

	public function testCaseAndInflectionParse() {
		$testRoute = array('controller' => 'PERMIT_TESTS');
		$result = $this->protectedMethodCall($this->Permit, '_parse', array($testRoute));
		$this->assertTrue($result);

		$this->Controller->params = array(
			'pass' => array(),
			'named' => array(),
			'plugin' => '', 'controller' => 'blog_permit_tests',
			'action' => 'INDEX'
		);
		$testRoute = array('controller' => 'PERMIT_TESTS');
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));

		$testRoute = array('controller' => 'Blog_PERMIT_TESTS');
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));

		$testRoute = array('action' => 'inDex');
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));
	}

	public function testNumberIndex() {
		$this->Controller->params = $this->Permit->_requestParams = array(
			'controller' => 'pages',
			'action' => 'display',
			'home'
		);
		$testRoute = array(
			'controller' => 'pages',
			'action' => 'display',
			(int)0 => 'home'
		);
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_parse', array($testRoute)));
	}

	public function testDenyAccess() {
		$this->Controller->Permit->settings['path'] = 'MockAuthTest.User';
		$this->Controller->Permit->settings['check'] = 'id';

		$testRoute = array('rules' => array());
		$this->assertNull($this->Permit->executed);
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->Permit->executed = null;

		$testRoute = array('rules' => array('deny' => true));
		$this->assertNull($this->Permit->executed);
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);

		$testRoute = array('rules' => array('deny' => false));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
	}

	public function testAuthenticatedUser() {
		$this->Permit->settings['path'] = 'MockAuthTest.Member';
		$this->Permit->settings['check'] = 'id';

		$testRoute = array('rules' => array('auth' => true));
		$this->assertNull($this->Permit->executed);
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);

		$this->Permit->settings['path'] = 'MockAuthTest.User';
		$testRoute = array('rules' => array('auth' => false));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
	}

	public function testNoUser() {
		$this->Permit->settings['path'] = 'MockAuthTest.Member';
		$this->Permit->settings['check'] = 'id';

		$testRoute = array('rules' => array('auth' => array('group' => 'member')));
		$this->assertNull($this->Permit->executed);
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
	}

/**
 * Setup a simple, single dim path/check
 * but will not be able to check on associated data
 */
	public function testAuthSingleDimensionExecute() {
		$this->Permit->settings['path'] = 'MockAuthTest.User';
		$this->Permit->settings['check'] = 'id';
		$this->Permit->user = $this->Permit->Session->read($this->Permit->settings['path']);

		# test bool, is logged in
		$testRoute = array('rules' => array('auth' => true));
		$this->assertNull($this->Permit->executed);
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);

		# test single field matches (false response = authorized)
		$testRoute = array('rules' => array('auth' => array('group' => 'member')));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('group' => 'nonmember')));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);

		$testRoute = array('rules' => array('auth' => array('id' => 'user-logged-in')));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('id' => 'user-alt')));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('id' => '*user*')));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('id' => '%user%')));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
	}

/**
 * Setup a full, milti dim path/check
 * WILL be able to check on associated data
 */
	public function testAuthMultidimensionalExecute() {
		$this->Permit->settings['path'] = 'MockAuthTest';
		$this->Permit->settings['check'] = 'User.id';
		$this->Permit->user = $this->Permit->Session->read($this->Permit->settings['path']);

		# test no rules
		$testRoute = array('rules' => array('notAuth' => true));
		$this->assertNull($this->Permit->executed);
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);

		# test auth is not an array or boolean
		$testRoute = array('rules' => array('auth' => 'notArrayOrBoolean'));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);

		# test bool, is logged in
		$testRoute = array('rules' => array('auth' => true));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);

		# test single field matches (false response = authorized)
		$testRoute = array('rules' => array('auth' => array('/User/group' => 'member')));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('/User/group' => 'nonmember')));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);

		$testRoute = array('rules' => array('auth' => array('/User/id' => 'user-logged-in')));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('/User/id' => 'user-alt')));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('/User/id' => '*user*')));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('/User/id' => '%user%')));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);

		$testRoute = array('rules' => array('auth' => array('/Role/name' => 'singleAssociation')));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('/Role/name' => 'something-else')));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);

		$testRoute = array('rules' => array('auth' => array('/Group/name' => 'admin')));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('/Group/name' => 'editors')));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('/Group/description' => 'HABTM association')));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('/Group/name' => 'something-else')));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);

		$testRoute = array('rules' => array('auth' => array('Group.name' => 'admin')));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('Group.name' => 'editors')));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('Group.description' => 'HABTM association')));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('Group.name' => 'something-else')));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);

		# test for passing multiple values in a field - default behavior should be 'and'
		$testRoute = array('rules' => array('auth' => array('Group.name' => array('something-else', 'something-else-2'))));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('Group.name' => array('admin', 'something-else'))));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('Group.name' => array('something-else', 'editors'))));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array('auth' => array('Group.name' => array('admin', 'editors'))));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);

		# test for passing multiple values in a field - using 'or' option
		$testRoute = array('rules' => array(
			'auth' => array('Group.name' => array(
				'or' => array('something-else', 'something-else-2')
			)),
		));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array(
			'auth' => array('Group.name' => array(
				'or' => array('admin', 'something-else-2')
			)),
		));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array(
			'auth' => array('Group.name' => array(
				'or' => array('something-else', 'editors')
			)),
		));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array(
			'auth' => array('Group.name' => array(
				'or' => array('admin', 'editors')
			)),
		));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);

		# test for passing multiple values in a field
		# using deprecated fields_behavior 'or' option
		$testRoute = array('rules' => array(
			'auth' => array('Group.name' => array('something-else', 'something-else-2')),
			'fields_behavior' => 'or'
		));
		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array(
			'auth' => array('Group.name' => array('admin', 'something-else-2')),
			'fields_behavior' => 'or'
		));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array(
			'auth' => array('Group.name' => array('something-else', 'editors')),
			'fields_behavior' => 'or'
		));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
		$testRoute = array('rules' => array(
			'auth' => array('Group.name' => array('admin', 'editors')),
			'fields_behavior' => 'or'
		));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);

		# Invalid fields_behavior setting
		$testRoute = array('rules' => array(
			'auth' => array('Group.name' => array('admin', 'editors')),
			'fields_behavior' => 'xor'
		));
		$this->assertFalse($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);
	}

	public function testStartup() {
		$this->Permit->settings['isTest'] = false;

		$this->Permit->access(
			array('controller' => 'permit_tests', 'action' => array('add', 'edit', 'delete')),
			array('auth' => true),
			array('redirect' => array('controller' => 'users', 'action' => 'login'))
		);
		$this->Permit->startup($this->Controller);
		$this->assertNull($this->Controller->testUrl);

		$this->Permit->access(
			array('controller' => 'users'),
			array('auth' => true),
			array(
				'element' => 'auth_error',
				'redirect' => array('controller' => 'users', 'action' => 'login')
			)
		);
		$this->Permit->startup($this->Controller);
		$this->assertNull($this->Controller->testUrl);

		$this->Permit->access(
			array('controller' => 'permit_tests', 'action' => 'index'),
			array('auth' => true),
			array('redirect' => array('controller' => 'users', 'action' => 'login'))
		);
		$this->Permit->startup($this->Controller);
		$this->assertEqual($this->Controller->testUrl, '/users/login');
		$this->Controller->testUrl = null;

		$this->Controller->params = array(
			'pass' => array(),
			'named' => array(),
			'plugin' => '', 'controller' => 'blog_permit_tests',
			'action' => 'index'
		);
		$this->Permit->startup($this->Controller);
		$this->Permit->access(
			array('controller' => 'blogPermitTests', 'action' => 'index'),
			array('auth' => true),
			array('redirect' => array('controller' => 'users', 'action' => 'login'))
		);
		$this->Permit->startup($this->Controller);
		$this->assertEqual($this->Controller->testUrl, '/users/login');
	}

	public function testAccess() {
		$this->assertEqual(count($this->Permit->routes), 0);
		$this->Permit->access(
			array('controller' => 'permit_tests', 'action' => array('add', 'edit', 'delete')),
			array('auth' => array('group' => 'admin')),
			array('redirect' => array('controller' => 'users', 'action' => 'login'))
		);
		$this->assertEqual(count($this->Permit->routes), 1);

		$this->Permit->access(
			array('controller' => 'users'),
			array('auth' => true),
			array(
				'element' => 'auth_error',
				'redirect' => array('controller' => 'users', 'action' => 'login')
			)
		);
		$this->assertEqual(count($this->Permit->routes), 2);

		$expected = array(
			'route' => array('controller' => 'permit_tests', 'action' => array('add', 'edit', 'delete')),
			'rules' => array('auth' => array('group' => 'admin')),
			'redirect' => array('controller' => 'users', 'action' => 'login'),
			'message' => __('Access denied', true),
			'element' => 'default',
			'params' => array(),
			'key' => 'flash',
		);
		$this->assertEqual(current($this->Permit->routes), $expected);
		reset($this->Permit->routes);

		$expected = array(
			'route' => array('controller' => 'users'),
			'rules' => array('auth' => true),
			'redirect' => array('controller' => 'users', 'action' => 'login'),
			'message' => __('Access denied', true),
			'element' => 'auth_error',
			'params' => array(),
			'key' => 'flash',
		);
		$this->assertEqual(end($this->Permit->routes), $expected);
		reset($this->Permit->routes);
	}

	public function testRedirect() {
		$this->Permit->settings['path'] = 'MockAuthTest';
		$this->Permit->settings['check'] = 'User.id';

		# test bool, is logged in
		$testRoute = array(
			'route' => array('controller' => 'permit_tests', 'action' => array('add', 'edit', 'delete')),
			'rules' => array('auth' => array('group' => 'admin')),
			'redirect' => array('controller' => 'users', 'action' => 'login'),
			'message' => __('Access denied', true),
			'element' => 'error',
			'params' => array(),
			'key' => 'flash',
		);

		$this->assertTrue($this->protectedMethodCall($this->Permit, '_execute', array($testRoute)));
		$this->assertEqual($this->Permit->executed, $testRoute);

		$this->Permit->redirect($this->Controller, $testRoute);
		$this->assertEqual($this->Controller->testUrl, '/users/login');

		$session = $this->Controller->Session->read('Message');
		$this->assertEqual($session['flash']['message'], __('Access denied', true));
		$this->assertEqual($session['flash']['element'], 'error');
		$this->assertEqual(count($session['flash']['params']), 0);
	}

	public function testReferer() {
		$this->Controller->Session->write('Sanction.referer', array());
		$this->assertEqual('/', $this->Permit->referer());

		$this->Controller->Session->write('Sanction.referer', null);
		$this->assertFalse($this->Permit->referer());

		$this->Controller->Session->write('Sanction.referer', array('controller' => 'users', 'action' => 'login'));
		$this->assertEqual('/users/login', $this->Permit->referer());
	}

	public function testPermitObject() {
		Permit::$routes = array();
		Permit::$executed = null;
		$this->assertEqual(count(Permit::$routes), 0);

		Permit::access(
			array('controller' => array('permit_tests', 'tags'), 'action' => array('add', 'edit', 'delete')),
			array('auth' => array('group' => 'admin')),
			array('redirect' => array('controller' => 'users', 'action' => 'login'))
		);
		$this->assertEqual(count(Permit::$routes), 1);

		Permit::access(
			array('controller' => 'permit_tests', 'action' => array('add', 'edit', 'delete')),
			array('auth' => array('group' => 'admin')),
			array('redirect' => array('controller' => 'users', 'action' => 'login'))
		);
		$this->assertEqual(count(Permit::$routes), 2);

		Permit::access(
			array('controller' => 'users'),
			array('auth' => true),
			array(
				'element' => 'auth_error',
				'redirect' => array('controller' => 'users', 'action' => 'login')
			)
		);
		$this->assertEqual(count(Permit::$routes), 3);

		$expected = array(
			'route' => array('controller' => array('permit_tests', 'tags'), 'action' => array('add', 'edit', 'delete')),
			'rules' => array('auth' => array('group' => 'admin')),
			'redirect' => array('controller' => 'users', 'action' => 'login'),
			'message' => __('Access denied', true),
			'element' => 'default',
			'params' => array(),
			'key' => 'flash',
		);
		$this->assertEqual(current(Permit::$routes), $expected);
		reset(Permit::$routes);

		$expected = array(
			'route' => array('controller' => 'users'),
			'rules' => array('auth' => true),
			'redirect' => array('controller' => 'users', 'action' => 'login'),
			'message' => __('Access denied', true),
			'element' => 'auth_error',
			'params' => array(),
			'key' => 'flash',
		);
		$this->assertEqual(end(Permit::$routes), $expected);
		reset(Permit::$routes);
	}

}
