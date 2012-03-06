<?php
App::uses('UserDetail', 'Model');

/**
 * UserDetail Test Case
 *
 */
class UserDetailTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.user_detail',
		'app.user',
		'app.maintainer',
		'app.package',
		'plugin.categories.category',
		'app.favorite',
		'plugin.ratings.rating'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->UserDetail = ClassRegistry::init('UserDetail');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->UserDetail);

		parent::tearDown();
	}

/**
 * testCreateDefaults method
 *
 * @return void
 */
	public function testCreateDefaults() {
		$this->UserDetail->createDefaults(99);
		$result = $this->UserDetail->find('all', array(
			'conditions' => array('user_id' => 99),
			'order' => array('position ASC'),
		));
		$result = Set::extract('/UserDetail/field', $result);
		$expected = array(
			'user.firstname',
			'user.middlename',
			'user.lastname',
			'user.abbr-country-name',
			'user.abbr-region',
			'user.country-name',
			'user.location',
			'user.postal-code',
			'user.region',
			'user.timeoffset',
		);
		$this->assertEquals($expected, $result);
	}
/**
 * testGetSection method
 *
 * @return void
 */
	public function testGetSection() {
		$result = $this->UserDetail->getSection('4f471545-7118-4910-bcbc-1ec075f6eb27', 'user');
		$expected = array(
			'user' => array(
				'firstname' => 'Kyle',
				'middlename' => 'Timothy',
				'lastname' => 'Robinson Young',
				'country-name' => 'United States',
			),
		);
		$this->assertEquals($expected, $result);
	}
/**
 * testSaveSection method
 *
 * @return void
 */
	public function testSaveSection() {
		$result = $this->UserDetail->saveSection('4f471545-7118-4910-bcbc-1ec075f6eb27', array(
			'UserDetail' => array(
				'postal-code' => '95451'
			),
		), 'user');
		$this->assertTrue($result);
		$result = $this->UserDetail->getSection('4f471545-7118-4910-bcbc-1ec075f6eb27', 'user');
		$this->assertEquals('95451', $result['user']['postal-code']);
	}
}
