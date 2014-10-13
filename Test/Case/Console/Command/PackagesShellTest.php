<?php
App::uses('PackagesShell', 'Console/Command');

class PackagesShellTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.package',
		'app.tag',
		'app.tagged',
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Shell = $this->getMock(
			'PackagesShell',
			array('out')
		);
		$this->Shell->expects($this->any())
			->method('out')
			->will($this->returnValue(null));
		$this->Shell->initialize();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
	}

/**
 * testConvertContainFields
 *
 * @return void
 */
	public function testConvertContainFields() {
		$this->Shell->convertContainFields();
		$this->Shell->Package->contain(array('Tag'));
		$result = $this->Shell->Package->findById(1);
		$this->assertTrue($result['Package']['contains_model']);
		$this->assertTrue($result['Package']['contains_theme']);
	}

}
