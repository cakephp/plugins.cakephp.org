<?php
App::uses('Github', 'Model');

/**
 * Github Test Case
 *
 */
class GithubTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.github');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Github = ClassRegistry::init('Github');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Github);

		parent::tearDown();
	}

/**
 * testSavePackage method
 *
 * @return void
 */
	public function testSavePackage() {
		$Github = $this->getMock('Github', array('load', 'enqueue'), array(
			$this->Github->id,
			$this->Github->useTable,
			$this->Github->useDbConfig,
		));
		$Github->expects($this->once())
			->method('load')
			->with(
				$this->equalTo('NewPackageJob'),
				$this->equalTo('shama'),
				$this->equalTo('Kyle Robinson Young')
			)
			->will($this->returnValue(true));
		$Github->expects($this->once())->method('enqueue');
		$Github->savePackage('shama', 'Kyle Robinson Young');
	}
}
