<?php
/**
 * UserDetailFixture
 *
 */
class UserDetailFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'user_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'position' => array('type' => 'float', 'null' => false, 'default' => '1'),
		'field' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'value' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'input' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'data_type' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'label' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 128, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'UNIQUE_PROFILE_PROPERTY' => array('column' => array('field', 'user_id'), 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => '4f471545-7118-4910-bcbc-1ec075f6eb27',
			'user_id' => '4f471545-7118-4910-bcbc-1ec075f6eb27',
			'position' => 1,
			'field' => 'user.firstname',
			'value' => 'Kyle',
			'input' => 'text',
			'data_type' => 'string',
			'label' => 'First Name',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44'
		),
		array(
			'id' => '4f471545-7118-4910-bcbc-1ec075f6eb28',
			'user_id' => '4f471545-7118-4910-bcbc-1ec075f6eb27',
			'position' => 2,
			'field' => 'user.middlename',
			'value' => 'Timothy',
			'input' => 'text',
			'data_type' => 'string',
			'label' => 'Middle Name',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44'
		),
		array(
			'id' => '4f471545-7118-4910-bcbc-1ec075f6eb29',
			'user_id' => '4f471545-7118-4910-bcbc-1ec075f6eb27',
			'position' => 3,
			'field' => 'user.lastname',
			'value' => 'Robinson Young',
			'input' => 'text',
			'data_type' => 'string',
			'label' => 'Last Name',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44'
		),
		array(
			'id' => '4f471545-7118-4910-bcbc-1ec075f6eb30',
			'user_id' => '4f471545-7118-4910-bcbc-1ec075f6eb27',
			'position' => 4,
			'field' => 'user.country-name',
			'value' => 'United States',
			'input' => 'text',
			'data_type' => 'string',
			'label' => 'Country Name',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44'
		),
	);
}
