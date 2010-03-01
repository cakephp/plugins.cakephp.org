<?php
/* Maintainer Fixture generated on: 2010-02-11 04:02:03 : 1265880123 */
class MaintainerFixture extends CakeTestFixture {
	var $name = 'Maintainer';

	var $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 50, 'key' => 'index'),
		'alias' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50, 'key' => 'index'),
		'name' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50, 'key' => 'index'),
		'url' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'twitter_username' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 15, 'key' => 'index'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'name' => array('column' => 'name', 'unique' => 0),
			'twitter_username' => array('column' => 'twitter_username', 'unique' => 0),
			'alias' => array('column' => 'alias', 'unique' => 0),
			'name' => array('column' => 'name', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	var $records = array(
		array(
			'id' => 1,
			'name' => 'Lorem ipsum dolor sit amet',
			'alias' => 'Lorem ipsum dolor sit amet',
			'name' => 'Lorem ipsum dolor sit amet',
			'url' => 'Lorem ipsum dolor sit amet',
			'twitter_username' => 'Lorem ipsum d',
		),
	);
}
?>