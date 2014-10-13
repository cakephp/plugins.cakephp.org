<?php
App::uses('Maintainer', 'Model');

/**
 * Maintainer Test Case
 *
 */
class MaintainerTestCase extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.maintainer',
		'app.user',
		'app.user_detail',
		'plugin.ratings.rating',
		'app.package',
		'app.category',
		'plugin.favorites.favorite'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Maintainer = ClassRegistry::init('Maintainer');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Maintainer);

		parent::tearDown();
	}

/**
 * testSeoView method
 *
 * @return void
 */
	public function testSeoView() {
		$data = $this->Maintainer->findByUsername('shama');
		$result = $this->Maintainer->seoView($data);
		$expected = array(
			'Kyle Robinson Young | CakePHP Package Maintainer | CakePackages',
			'Kyle Robinson Young - CakePHP Package on CakePackages',
			'cakephp package | cakephp'
		);
		$this->assertEquals($expected, $result);
	}
}
