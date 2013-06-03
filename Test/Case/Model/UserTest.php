<?php
App::uses('User', 'Model');
App::uses('AuthComponent', 'Controller/Component');

/**
 * User Test Case
 *
 */
class UserTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.user',
		'app.maintainer',
		'app.package',
		'app.category',
		'plugin.favorites.favorite',
		'plugin.ratings.rating',
		'app.user_detail'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->User = ClassRegistry::init('User');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->User);
		parent::tearDown();
	}

/**
 * testValidatePassword method
 *
 * @return void
 */
	public function testValidatePassword() {
		$this->User->data['User']['passwd'] = '1234';
		$this->assertTrue($this->User->validatePassword(array('temppassword' => '1234')));
		$this->assertFalse($this->User->validatePassword(array('temppassword' => 'wrong!')));
		$this->assertFalse($this->User->validatePassword(array('temppassword' => '')));
	}
/**
 * testValidateEmail method
 *
 * @return void
 */
	public function testValidateEmail() {
		$this->User->data['User']['email'] = 'test@example.com';
		$this->assertTrue($this->User->validateEmail(array('confirm_email' => 'test@example.com')));
		$this->assertFalse($this->User->validateEmail(array('confirm_email' => 'tast@example.com')));
	}
/**
 * testChangeActivationKey method
 *
 * @return void
 */
	public function testChangeActivationKey() {
		$user = $this->User->findById('4f471545-7118-4910-bcbc-1ec075f6eb27');
		$token = $this->User->changeActivationKey($user['User']);
		$userNow = $this->User->findById('4f471545-7118-4910-bcbc-1ec075f6eb27');
		$this->assertNotEquals($user['User']['password_token'], $token);
		$this->assertNotEquals($user['User']['password_token'], $userNow['User']['password_token']);
		$this->assertNotEquals($user['User']['email_token_expires'], $userNow['User']['email_token_expires']);
	}
/**
 * testForgotPassword method
 *
 * @return void
 */
	public function testForgotPassword() {
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$data = $this->User->findById('4f471545-7118-4910-bcbc-1ec075f6eb27');
		$User = $this->getMock('User', array('enqueue'), array(
			$this->User->id,
			$this->User->useTable,
			$this->User->useDbConfig,
		));
		$User->alias = 'User';
		$User->expects($this->once())
			->method('enqueue')
			->with(
				$this->equalTo('UserForgotPasswordJob'),
				$this->equalTo(array(array(
					'user' => $data['User'],
					'ipaddress' => $_SERVER['REMOTE_ADDR']
				)))
			)
			->will($this->returnValue(true));
		$User->expects($this->once())->method('enqueue');
		$User->forgotPassword($data);
	}
/**
 * testIsValidToken method
 *
 * @return void
 */
	public function testIsValidToken() {
		$now = strtotime('2012-02-24 04:42:45') - strtotime('-1 hour');

		// invalid token
		$this->assertFalse($this->User->isValidToken('notavalidtoken'));

		// valid w/o reset
		$res = $this->User->isValidToken('abcd1234', false, $now);
		$this->assertEquals('', $res['User']['email_token']);
		$this->assertEquals('', $res['User']['email_token_expires']);

		// valid and reset
		$user = $this->User->findById('4f471545-7118-4910-bcbc-1ec075f6eb27');
		$res = $this->User->isValidToken('abcd1234', true, $now);
		$this->assertEquals('', $res['User']['password_token']);
		$this->assertTrue(!empty($res['User']['new_password']));
		$this->assertNotEquals($user['User']['passwd'], $res['User']['passwd']);
	}
/**
 * testLoggedIn method
 *
 * @return void
 */
	public function testLoggedIn() {
		$User = $this->getMock('User', array('saveField'), array(
			$this->User->id,
			$this->User->useTable,
			$this->User->useDbConfig,
		));
		$User->alias = 'User';
		$User->expects($this->once())
			->method('saveField')
			->with($this->equalTo('last_login'), $this->anything());
		$User->loggedIn();
	}
/**
 * testRegister method
 *
 * @return void
 */
	public function testRegister() {
		$data = array(
			'User' => array(
				'username' => 'newguy',
				'email' => 'dude@cakephp.org',
				'passwd' => '1234',
				'temppassword' => '1234',
				'tos' => 1,
			),
		);
		$User = $this->getMock('User', array('_sendVerificationEmail'), array(
			$this->User->id,
			$this->User->useTable,
			$this->User->useDbConfig,
		));
		$User->alias = 'User';
		$User->expects($this->once())->method('_sendVerificationEmail');
		$result = $User->register($data);
		$this->assertTrue(!empty($result['User']['id']));
		$this->assertEquals(0, $result['User']['email_authenticated']);
	}
/**
 * testResetPassword method
 *
 * @return void
 */
	public function testResetPassword() {
		$data = array(
			'User' => array(
				'id' => '4f471545-7118-4910-bcbc-1ec075f6eb27',
				'new_password' => 'thisismynewpassword',
				'confirm_password' => 'thisismynewpassword',
			),
		);
		$result = $this->User->resetPassword($data);
		$this->assertTrue(!empty($result['User']['passwd']));
		$this->assertEquals('', $result['User']['password_token']);
	}
/**
 * testIsValidEmail method
 *
 * @return void
 */
	public function testIsValidEmail() {
		$result = $this->User->isValidEmail('verifyme1234');
		$this->assertEquals(1, $result['User']['email_authenticated']);
		$this->assertEquals(1, $result['User']['active']);
		$this->assertEquals('', $result['User']['email_token']);
		$this->assertEquals('', $result['User']['email_token_expires']);
	}
}
