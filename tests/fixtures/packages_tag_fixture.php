<?php
/* PackagesTag Fixture generated on: 2010-03-07 01:03:53 : 1267926773 */
class PackagesTagFixture extends CakeTestFixture {
	var $name = 'PackagesTag';

	var $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'package_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'tag_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'tag_id' => array('column' => 'tag_id', 'unique' => 0), 'package_id' => array('column' => 'package_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	var $records = array(
		array(
			'id' => 1,
			'package_id' => 53,
			'tag_id' => 1
		),
		array(
			'id' => 2,
			'package_id' => 53,
			'tag_id' => 2
		),
		array(
			'id' => 3,
			'package_id' => 53,
			'tag_id' => 3
		),
		array(
			'id' => 4,
			'package_id' => 53,
			'tag_id' => 4
		),
		array(
			'id' => 5,
			'package_id' => 53,
			'tag_id' => 5
		),
		array(
			'id' => 6,
			'package_id' => 91,
			'tag_id' => 6
		),
		array(
			'id' => 7,
			'package_id' => 91,
			'tag_id' => 7
		),
		array(
			'id' => 8,
			'package_id' => 91,
			'tag_id' => 8
		),
		array(
			'id' => 9,
			'package_id' => 91,
			'tag_id' => 9
		),
		array(
			'id' => 52,
			'package_id' => 100,
			'tag_id' => 19
		),
		array(
			'id' => 46,
			'package_id' => 1,
			'tag_id' => 15
		),
		array(
			'id' => 45,
			'package_id' => 1,
			'tag_id' => 14
		),
		array(
			'id' => 44,
			'package_id' => 1,
			'tag_id' => 13
		),
		array(
			'id' => 43,
			'package_id' => 1,
			'tag_id' => 12
		),
		array(
			'id' => 42,
			'package_id' => 1,
			'tag_id' => 11
		),
		array(
			'id' => 41,
			'package_id' => 1,
			'tag_id' => 10
		),
		array(
			'id' => 51,
			'package_id' => 100,
			'tag_id' => 18
		),
		array(
			'id' => 50,
			'package_id' => 100,
			'tag_id' => 17
		),
		array(
			'id' => 62,
			'package_id' => 277,
			'tag_id' => 5
		),
		array(
			'id' => 61,
			'package_id' => 277,
			'tag_id' => 23
		),
		array(
			'id' => 60,
			'package_id' => 277,
			'tag_id' => 22
		),
		array(
			'id' => 59,
			'package_id' => 277,
			'tag_id' => 21
		),
		array(
			'id' => 58,
			'package_id' => 277,
			'tag_id' => 20
		),
	);
}
?>