<?php
/**
 * MaintainerFixture
 *
 */
class MaintainerFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'key' => 'index'),
		'group' => array('type' => 'string', 'null' => false, 'default' => 'maintainer', 'length' => 20, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'username' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'key' => 'unique', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'email' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 50, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'alias' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'url' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'twitter_username' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 15, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'company' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'location' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'gravatar_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'password' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 40, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'activation_key' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 40, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'username' => array('column' => 'username', 'unique' => 1), 'name' => array('column' => 'name', 'unique' => 0), 'activation_key' => array('column' => 'activation_key', 'unique' => 0), 'user_id' => array('column' => 'user_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'user_id' => '4f471545-27a8-4ad7-89c9-1ec075f6eb26',
			'group' => 'group1',
			'username' => 'savant',
			'email' => 'savant@areyousmokingcrack.com',
			'name' => 'Jose Diaz-Gonzalez',
			'alias' => 'savant',
			'url' => 'http://github.com/josegonzalez',
			'twitter_username' => 'savant',
			'company' => 'Troll, Inc',
			'location' => 'New York?',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44',
			'gravatar_id' => 'Lorem ipsum dolor sit amet',
			'password' => 'Lorem ipsum dolor sit amet',
			'activation_key' => 'Lorem ipsum dolor sit amet'
		),
		array(
			'id' => 2,
			'user_id' => '4f471545-7118-4910-bcbc-1ec075f6eb27',
			'group' => 'group1',
			'username' => 'shama',
			'email' => 'shama@dontkry.com',
			'name' => 'Kyle Robinson Young',
			'alias' => 'shama',
			'url' => 'http://github.com/shama',
			'twitter_username' => 'kyletyoung',
			'company' => 'Unemployed',
			'location' => 'The Internet',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44',
			'gravatar_id' => 'Lorem ipsum dolor sit amet',
			'password' => 'Lorem ipsum dolor sit amet',
			'activation_key' => 'Lorem ipsum dolor sit amet'
		),
	);
}
