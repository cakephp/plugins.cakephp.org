<?php
/* Tag Fixture generated on: 2010-03-07 01:03:53 : 1267926773 */
class TagFixture extends CakeTestFixture {
	var $name = 'Tag';

	var $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 40, 'key' => 'index'),
		'packages_count' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'name' => array('column' => 'name', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	var $records = array(
		array(
			'id' => 1,
			'name' => 'api',
			'packages_count' => 1
		),
		array(
			'id' => 2,
			'name' => 'official',
			'packages_count' => 1
		),
		array(
			'id' => 3,
			'name' => 'cakephp',
			'packages_count' => 1
		),
		array(
			'id' => 4,
			'name' => 'generation',
			'packages_count' => 1
		),
		array(
			'id' => 5,
			'name' => 'plugin',
			'packages_count' => 3
		),
		array(
			'id' => 6,
			'name' => 'whodidit',
			'packages_count' => 1
		),
		array(
			'id' => 7,
			'name' => 'record',
			'packages_count' => 1
		),
		array(
			'id' => 8,
			'name' => 'tracking',
			'packages_count' => 1
		),
		array(
			'id' => 9,
			'name' => 'behavior',
			'packages_count' => 1
		),
		array(
			'id' => 10,
			'name' => 'ajax',
			'packages_count' => 1
		),
		array(
			'id' => 11,
			'name' => 'validation',
			'packages_count' => 1
		),
		array(
			'id' => 12,
			'name' => 'jquery',
			'packages_count' => 1
		),
		array(
			'id' => 13,
			'name' => 'form',
			'packages_count' => 1
		),
		array(
			'id' => 14,
			'name' => 'helper',
			'packages_count' => 1
		),
		array(
			'id' => 15,
			'name' => 'component',
			'packages_count' => 1
		),
		array(
			'id' => 16,
			'name' => 'lol',
			'packages_count' => 0
		),
		array(
			'id' => 17,
			'name' => 'application',
			'packages_count' => 1
		),
		array(
			'id' => 18,
			'name' => 'site',
			'packages_count' => 1
		),
		array(
			'id' => 19,
			'name' => 'refactor',
			'packages_count' => 1
		),
		array(
			'id' => 20,
			'name' => 'acl',
			'packages_count' => 2
		),
		array(
			'id' => 21,
			'name' => 'unittest',
			'packages_count' => 2
		),
		array(
			'id' => 22,
			'name' => 'cakeshell',
			'packages_count' => 2
		),
		array(
			'id' => 23,
			'name' => 'shell',
			'packages_count' => 2
		),
	);
}
?>