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
App::import('Component', array('Sanction.Permit', 'Session'));

/**
* TestAuthComponent class
*
* @package       cake
* @subpackage    cake.tests.cases.libs.controller.components
*/
class TestPermitComponent extends PermitComponent {

/**
 * testStop property
 *
 * @var bool false
 * @access public
 */
	var $testStop = false;

/**
 * Sets default login state
 *
 * @var bool true
 * @access protected
 */
	var $_loggedIn = true;

/**
 * stop method
 *
 * @access public
 * @return void
 */
	function _stop() {
		$this->testStop = true;
	}
}

/**
* PermitTestController class
*
* @package       cake
* @subpackage    cake.tests.cases.libs.controller.components
*/
class PermitTestController extends Controller {

/**
 * name property
 *
 * @var string 'AuthTest'
 * @access public
 */
	var $name = 'AuthTest';

/**
 * uses property
 *
 * @var array
 * @access public
 */
	var $uses = array();

/**
 * components property
 *
 * @var array
 * @access public
 */
	var $components = array('Sanction.Permit', 'Session');

/**
 * testUrl property
 *
 * @var mixed null
 * @access public
 */
	var $testUrl = null;

/**
 * construct method
 *
 * @access private
 * @return void
 */
	function __construct() {
		$this->params = Router::parse('/auth_test');
		Router::setRequestInfo(array($this->params, array('base' => null, 'here' => '/auth_test', 'webroot' => '/', 'passedArgs' => array(), 'argSeparator' => ':', 'namedArgs' => array())));
		parent::__construct();
	}

/**
 * beforeFilter method
 *
 * @access public
 * @return void
 */
	function beforeFilter() {
	}

/**
 * login method
 *
 * @access public
 * @return void
 */
	function login() {
	}

/**
 * admin_login method
 *
 * @access public
 * @return void
 */
	function admin_login() {
	}

/**
 * logout method
 *
 * @access public
 * @return void
 */
	function logout() {
		// $this->redirect($this->Auth->logout());
	}

/**
 * add method
 *
 * @access public
 * @return void
 */
	function add() {
		echo "add";
	}

/**
 * add method
 *
 * @access public
 * @return void
 */
	function camelCase() {
		echo "camelCase";
	}

/**
 * redirect method
 *
 * @param mixed $url
 * @param mixed $status
 * @param mixed $exit
 * @access public
 * @return void
 */
	function redirect($url, $status = null, $exit = true) {
		$this->testUrl = Router::url($url);
		return false;
	}


/**
 * Mock delete method
 *
 * @param mixed $url
 * @param mixed $status
 * @param mixed $exit
 * @access public
 * @return void
 */
	function delete($id = null) {
		echo 'delete';
	}
}

/**
* PermitTest class
*
* @package       cake
* @subpackage    cake.tests.cases.libs.controller.components
*/
class PermitTest extends CakeTestCase {

/**
 * name property
 *
 * @var string 'Auth'
 * @access public
 */
	var $name = 'Permit';

/**
 * initialized property
 *
 * @var bool false
 * @access public
 */
	var $initialized = false;

/**
 * startTest method
 *
 * @access public
 * @return void
 */
	function startTest() {
		$this->Controller =& new PermitTestController();
		restore_error_handler();

		$this->Controller->constructClasses();
		$this->Controller->params = array(
			'pass' => array(),  'named' => array(), 
			'plugin' => '', 'controller' => 'posts', 
			'action' => 'index'
		);
		$this->Controller->Component->initialize($this->Controller);
		$this->Controller->beforeFilter();
		$this->Controller->Component->startup($this->Controller);

		set_error_handler('simpleTestErrorHandler');
		//$this->Controller->Permit->startup($this->Controller);
		ClassRegistry::addObject('view', new View($this->Controller));

		$this->Controller->Session->delete('Auth');
		$this->Controller->Session->delete('Message.auth');

		Router::reload();

		$this->initialized = true;
	}

/**
 * endTest method
 *
 * @access public
 * @return void
 */
	function endTest() {
		$this->Controller->Component->shutdown($this->Controller);
		$this->Controller->afterFilter();

		$this->Controller->Session->delete('Auth');
		$this->Controller->Session->delete('Message.auth');
		ClassRegistry::flush();
		unset($this->Controller);
	}

	function testSingleParse() {
		$testRoute = array('controller' => 'posts');
		$this->assertTrue($this->Controller->Permit->parse($testRoute));

		$testRoute = array('controller' => 'posts', 'action' => 'index');
		$this->assertTrue($this->Controller->Permit->parse($testRoute));

		$testRoute = array('plugin' => null, 'controller' => 'posts', 'action' => 'index');
		$this->assertTrue($this->Controller->Permit->parse($testRoute));

		$testRoute = array('controller' => 'posts', 'action' => 'add');
		$this->assertFalse($this->Controller->Permit->parse($testRoute));

		$testRoute = array('controller' => 'users', 'action' => 'index');
		$this->assertFalse($this->Controller->Permit->parse($testRoute));
	}

	function testMultipleParse() {
		$testRoute = array('controller' => 'posts', 'action' => array('index'));
		$this->assertTrue($this->Controller->Permit->parse($testRoute));

		$testRoute = array('controller' => 'posts', 'action' => array('index', 'add'));
		$this->assertTrue($this->Controller->Permit->parse($testRoute));

		$testRoute = array('controller' => array('posts', 'users'), 'action' => array('index', 'add'));
		$this->assertTrue($this->Controller->Permit->parse($testRoute));

		$testRoute = array('plugin' => array(null, 'blog'), 
			'controller' => array('posts', 'users'), 
			'action' => array('index', 'add')
		);
		$this->assertTrue($this->Controller->Permit->parse($testRoute));

		$testRoute = array('controller' => 'posts', 'action' => array('add', 'edit', 'delete'));
		$this->assertFalse($this->Controller->Permit->parse($testRoute));
	}
}