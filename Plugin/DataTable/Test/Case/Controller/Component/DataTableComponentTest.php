<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('ComponentCollection', 'Controller');
App::uses('DataTableComponent', 'DataTable.Controller/Component');
/**
 * DataTable Component Test
 */
class DataTableComponentTest extends CakeTestCase {

	/**
	 * fixtures
	 *
	 * @var array
	 */
	public $fixtures = array(
		'core.post',
	);

	/**
	 * setUp
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		putenv('REQUEST_METHOD=GET');
		$settings = array(
			'Post' => array(
				'columns' => array(
					'id' => false,
					'title' => 'Post Title',
					'body' => true,
					'created' => array(
						'bSearchable' => false,
					),
					'Action' => null,
				),
			),
		);

		$this->request = new CakeRequest();
		$this->request->query = array(
			'sEcho' => 1,
			'iColumns' => 4,
			'sColumns' => '',
			'iDisplayStart' => 0,
			'iDisplayLength' => 10,
			'mDataProp_0' => 0,
			'mDataProp_1' => 1,
			'mDataProp_2' => 2,
			'mDataProp_3' => 3,
			'sSearch' => '',
			'bRegex' => false,
			'sSearch_0' => '',
			'bRegex_0' => false,
			'bSearchable_0' => true,
			'sSearch_1' => '',
			'bRegex_1' => false,
			'bSearchable_1' => true,
			'sSearch_2' => '',
			'bRegex_2' => false,
			'bSearchable_2' => true,
			'sSearch_3' => '',
			'bRegex_3' => false,
			'bSearchable_3' => true,
			'iSortCol_0' => 0,
			'sSortDir_0' => 'desc',
			'iSortingCols' => 1,
			'bSortable_0' => true,
			'bSortable_1' => true,
			'bSortable_2' => true,
			'bSortable_3' => true,
		);

		$Controller = new Controller($this->request, new CakeResponse());
		$Collection = new ComponentCollection();

		$this->DataTable = new DataTableComponent($Collection, $settings);
		$this->DataTable->initialize($Controller);
	}

	/**
	 * tearDown
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		unset($this->request);
		unset($this->DataTable);
	}

	/**
	 * testConfig
	 *
	 * @return void
	 */
	public function testConfig() {
		$config = $this->DataTable->paginate('Post');

		$fields = array(
			'Post.id',
			'Post.title',
			'Post.body',
			'Post.created',
		);
		$this->assertEqual($fields, $config->fields);

		$parsedColumnConfig = array(
			'Post.id' => array(
				'useField' => true,
				'label' => 'Id',
				'bSortable' => false,
				'bSearchable' => false
			),
			'Post.title' => array(
				'useField' => true,
				'label' => 'Post Title',
				'bSortable' => true,
				'bSearchable' => true
			),
			'Post.body' => array(
				'useField' => true,
				'label' => 'Body',
				'bSortable' => true,
				'bSearchable' => true
			),
			'Post.created' => array(
				'useField' => true,
				'label' => 'Created',
				'bSortable' => true,
				'bSearchable' => false
			),
			'Action' => array(
				'useField' => false,
				'label' => 'Action',
				'bSortable' => false,
				'bSearchable' => false
			),
		);

		$this->assertEqual($parsedColumnConfig, $config->columns);
	}

	/**
	 * testUnsortableColumn
	 *
	 * @return void
	 */
	public function testUnsortableColumn() {
		$this->request->query['iSortCol_0'] = 0;
		$config = $this->DataTable->paginate('Post');
		$this->assertEmpty($config->order);
	}

	/**
	 * testSortableColumn
	 *
	 * @return void
	 */
	public function testSortableColumn() {
		$this->request->query['iSortCol_0'] = 3;
		$this->request->query['iSortDir_0'] = 'desc';
		$config = $this->DataTable->paginate('Post');
		$this->assertEqual(array('Post.created' => 'desc'), $config->order);
	}

	/**
	 * testSearch
	 *
	 * @return void
	 */
	public function testSearch() {
		$this->request->query['sSearch_1'] = 'test';
		$config = $this->DataTable->paginate('Post');

		$conditions = array(
			'OR' => array(
				array('Post.body LIKE' => '%test%'),
			),
		);
		$this->assertEqual($conditions, $config->conditions);
	}

	/**
	 * testPaginate
	 *
	 * @return void
	 */
	public function testPaginate() {
		$config = $this->DataTable->paginate('Post');
		$this->assertEqual(10, $config->limit);
		$this->assertEqual(0, $config->offset);

		$this->request->query['iDisplayStart'] = 10;
		$config = $this->DataTable->paginate('Post');
		$this->assertEqual(10, $config->limit);
		$this->assertEqual(10, $config->offset);
	}
}