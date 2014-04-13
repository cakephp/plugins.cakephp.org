<?php
App::uses('Environment', 'Environments.Lib');

class EnvironmentTest extends CakeTestCase {

	public function setUp() {
		parent::setUp();

		Configure::write('debug', 0);
		$this->Environment = Environment::getInstance();

		$envs = array(
			array(
				'name' => 'staging',
				'params' => array(
					'SERVER_NAME' => 'example.tld'
				),
				'config' => array(
					'debug' => 2,
					'Session.name' => 'staging-session',
					'security' => 'low'
				),
				'callable' => null
			),
			array(
				'name' => 'production',
				'params' => array(
					'SERVER_NAME' => 'production.tld',
					'SERVER_ADDR' => '8.8.8.8'
				),
				'config' => array(
					'debug' => 1,
					'Session.name' => 'production-session'
				),
				'callable' => null
			),
			array(
				'name' => 'preproduction',
				'params' => array(
					'SERVER_NAME' => array('preproduction.tld', 'preprod.local')
				),
				'config' => array(
					'debug' => 1,
					'Session.name' => 'preproduction-session'
				),
				'callable' => function() {
					Configure::write('Environment.callback', true);
				}
			),
			array(
				'name' => 'dev1',
				'params' => false,
				'config' => array(),
				'callable' => null
			),
			array(
				'name' => 'dev2',
				'params' => array(
					'is_bool' => 'Hello, World!'
				),
				'config' => array(),
				'callable' => array()
			)
		);

		foreach ($envs as $env) {
			Environment::configure($env['name'], $env['params'], $env['config'], $env['callable']);
		}

		Configure::read('Environment.setup', false);
		$_SERVER['CAKE_ENV'] = null;
	}

	public function tearDown() {
		parent::tearDown();
		unset($this->Environment, $_SERVER['CAKE_ENV']);
	}

	public function testConfigure() {
		$this->assertArrayHasKey('staging', $this->Environment->environments);
		$this->assertArrayHasKey('production', $this->Environment->environments);
	}

/**
 * @expectedException CakeException
 * @expectedExceptionMessage Environment development does not exist.
 */
	public function testStart() {
		Environment::start();
	}

/**
 * Test whether the environment falls back to default, if nothing is matched
 */
	public function testStartDefault() {
		Environment::start(null, 'staging');
		$this->assertEquals('staging', Configure::read('Environment.name'));
	}

/**
 * Test that the environment setup returns false, as the setup is finished already.
 */
	public function testStartSetupFinished() {
		Configure::write('Environment.setup', true);
		$this->assertFalse(Environment::start());
	}

/**
 * Test that the environment falls back to staging, since one of the
 * config attributes doesn't match
 */
	public function testStartFalseAttribute() {
		$_SERVER['SERVER_NAME'] = 'production.tld';
		$_SERVER['SERVER_ADDR'] = '255.255.255.255';

		Environment::start(null, 'staging');
		$this->assertEquals('staging', Configure::read('Environment.name'));
	}

/**
 * Testing in_array in config array
 */
	public function testStartInArray() {
		Configure::write('Environment.callback', false);
		$_SERVER['SERVER_NAME'] = 'preprod.local';
		Environment::start();

		$this->assertEquals('preproduction', Configure::read('Environment.name'));
		$this->assertEquals('preproduction', Environment::is());
		$this->assertTrue(Environment::is('preproduction'));
		$this->assertEquals('preproduction-session', Configure::read('Session.name'));
		$this->assertTrue(Configure::read('Environment.callback'));
	}

/**
 * Test whether the CAKE_ENV works
 */
	public function testStartEnv() {
		$_SERVER['CAKE_ENV'] = 'production';
		Environment::start(null, 'staging');

		$this->assertTrue(Environment::is('production'));
	}

/**
 * Test the bool attribute
 */
	public function testStartBool() {
		Environment::configure('dev1', true, array(), null);
		Environment::start(null, 'staging');
		$this->assertEquals('dev1', Environment::is());
	}

/**
 * Test whether functions in config works
 */
	public function testStartFunctions() {
		Environment::configure('dev2', array(
			'is_bool' => false
		), array(), null);
		Environment::start(null, 'staging');
		$this->assertEquals('dev2', Environment::is());
	}
}
