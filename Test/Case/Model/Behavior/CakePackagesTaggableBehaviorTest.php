<?php
App::uses('CakePackagesTaggableBehavior', 'Model/Behavior');
App::uses('CakeSession', 'Model/Datasource');

/**
 * Article model
 */
class Article extends CakeTestModel {

/**
 * Model name
 *
 * @var string
 */
	public $name = 'Article';

/**
 * Use table
 *
 * @var string
 */
	public $useTable = 'articles';

/**
 * Belongs to associations
 *
 * @var array
 */
	public $belongsTo = array();

/**
 * HABTM associations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array();

/**
 * Has Many Associations
 *
 * @var array
 */
	public $hasMany = array();

/**
 * Has One associations
 *
 * @var array
 */
	public $hasOne = array();

/**
 * Behaviors
 *
 * @var array
 */
	public $actsAs = array('CakePackagesTaggable');
}

/**
 * CakePackagesTaggableBehavior Test
 */
class CakePackagesTaggableBehaviorTest extends CakeTestCase {

/**
 * Fixtures associated with this test case
 *
 * @var array
 * @return void
 */
	public $fixtures = array(
		'app.tagged',
		'app.tag',
		'plugin.tags.article'
	);

/**
 * Method executed before each test
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Article = ClassRegistry::init('Article');
		$this->Article->Behaviors->attach('CakePackagesTaggable', array());
	}

/**
 * Method executed after each test
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Article);
		ClassRegistry::flush();
		CakeSession::delete('Auth');
	}

/**
 * testAsUser
 */
	public function testAsUser() {
		CakeSession::write('Auth', array(
			'User' => array(
				'id' => 1,
				'username' => 'shama',
				'email' => 'admin@example.com',
				'is_admin' => 0,
				'active' => 1,
			),
		));
		$data = array(
			'title' => 'Test Article',
			'tags' => 'attribute:behavior, neat, anus'
		);
		$this->Article->create();
		$this->Article->save($data, false);
		$result = $this->Article->findByTitle('Test Article');
		$this->assertEquals('behavior, neat', $result['Article']['tags']);
	}

/**
 * testAsAdmin
 */
	public function testAsAdmin() {
		CakeSession::write('Auth', array(
			'User' => array(
				'id' => 1,
				'username' => 'shama',
				'email' => 'admin@example.com',
				'is_admin' => 1,
				'active' => 1,
			),
		));
		$this->Article->Behaviors->load('CakePackagesTaggable');
		$this->Article->isAdmin(true);
		$data = array(
			'title' => 'Test Article',
			'tags' => 'contains:behavior, neat, anus'
		);
		$this->Article->create();
		$this->Article->save($data, false);
		$result = $this->Article->findByTitle('Test Article');
		$this->assertEquals('anus, neat', $result['Article']['tags']);
		$this->assertEquals('contains', $result['Tag'][2]['identifier']);
	}
}
