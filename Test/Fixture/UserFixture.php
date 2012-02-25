<?php
/**
 * UserFixture
 *
 */
class UserFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'username' => array('type' => 'string', 'null' => false, 'default' => NULL, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'slug' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'passwd' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 128, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'password_token' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 128, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'email' => array('type' => 'string', 'null' => true, 'default' => NULL, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'email_authenticated' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'email_token' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'email_token_expires' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'tos' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'active' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'last_login' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'last_activity' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'is_admin' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'role' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'BY_USERNAME' => array('column' => array('username', 'passwd'), 'unique' => 0), 'BY_EMAIL' => array('column' => array('email', 'passwd'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => '4f471545-27a8-4ad7-89c9-1ec075f6eb26',
			'username' => 'savant',
			'slug' => 'savant',
			'passwd' => '1234',
			'password_token' => '',
			'email' => 'savant@areyousmokingcrack.com',
			'email_authenticated' => 1,
			'email_token' => '',
			'email_token_expires' => '2012-02-24 04:42:45',
			'tos' => 1,
			'active' => 1,
			'last_login' => '2012-02-24 04:42:45',
			'last_activity' => '2012-02-24 04:42:45',
			'is_admin' => 1,
			'role' => 'troll',
			'created' => '2012-02-24 04:42:45',
			'modified' => '2012-02-24 04:42:45'
		),
		array(
			'id' => '4f471545-7118-4910-bcbc-1ec075f6eb27',
			'username' => 'shama',
			'slug' => 'shama',
			'passwd' => '1234',
			'password_token' => 'abcd1234',
			'email' => 'shama@yourmama.com',
			'email_authenticated' => 1,
			'email_token' => 'abcd1234',
			'email_token_expires' => '2012-02-24 04:42:45',
			'tos' => 1,
			'active' => 1,
			'last_login' => '2012-02-24 04:42:45',
			'last_activity' => '2012-02-24 04:42:45',
			'is_admin' => 1,
			'role' => 'typofixer',
			'created' => '2012-02-24 04:42:45',
			'modified' => '2012-02-24 04:42:45'
		),
		array(
			'id' => '4f471545-7118-4910-bcbc-1ec075f6eb28',
			'username' => 'unverified',
			'slug' => 'unverified',
			'passwd' => '1234',
			'password_token' => 'abcd1234',
			'email' => 'nobody@example.com',
			'email_authenticated' => 0,
			'email_token' => 'verifyme1234',
			'email_token_expires' => '3012-02-24 04:42:45',
			'tos' => 1,
			'active' => 0,
			'last_login' => '2012-02-24 04:42:45',
			'last_activity' => '2012-02-24 04:42:45',
			'is_admin' => 0,
			'role' => 'nobody',
			'created' => '2012-02-24 04:42:45',
			'modified' => '2012-02-24 04:42:45'
		),
	);
}
