<?php
App::uses('ApiPackage', 'Model');

/**
 * ApiPackage Test Case
 *
 */
class ApiPackageTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.api_package',
		'app.maintainer',
		'app.user',
		'app.user_detail',
		'plugin.ratings.rating',
		'app.package',
		'plugin.categories.category',
		'app.favorite',
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ApiPackage = ClassRegistry::init('ApiPackage');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ApiPackage);

		parent::tearDown();
	}

/**
 * testFindInstall
 */
	public function testFindInstall() {
		$result = $this->ApiPackage->find('install', array(
			'request' => array(
				'package' => 'chocolate',
			),
		));
		$expected = array(
			array(
				'Package' => array(
					'name' => 'chocolate',
					'description' => 'Lorem ipsum dolor sit amet',
					'last_pushed_at' => '2012-02-24 04:42:44',
				),
			),
		);
		$this->assertEquals($expected, $result);
	}

}
