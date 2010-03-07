<?php
/* SchemaMigration Fixture generated on: 2010-03-07 01:03:53 : 1267926773 */
class SchemaMigrationFixture extends CakeTestFixture {
	var $name = 'SchemaMigration';

	var $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'version' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'type' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 50),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	var $records = array(
		array(
			'id' => 1,
			'version' => 1,
			'type' => 'migrations',
			'created' => '2010-02-21 09:27:48'
		),
	);
}
?>