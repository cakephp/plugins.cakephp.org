<?php
/* Package Fixture generated on: 2010-02-11 04:02:03 : 1265880123 */
class PackageFixture extends CakeTestFixture {
	var $name = 'Package';

	var $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'maintainer_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'package_type_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'key' => 'index'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'bakery_article' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'homepage' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'description' => array('type' => 'string', 'null' => true, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'name' => array('column' => 'name', 'unique' => 0), 'maintainer_id' => array('column' => 'maintainer_id', 'unique' => 0), 'package_type_id' => array('column' => 'package_type_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	var $records = array(
		array(
			'id' => 1,
			'maintainer_id' => 1,
			'package_type_id' => 1,
			'name' => 'Lorem ipsum dolor sit amet',
			'bakery_article' => 'Lorem ipsum dolor sit amet',
			'homepage' => 'Lorem ipsum dolor sit amet',
			'description' => 'Lorem ipsum dolor sit amet'
		),
	);
}
?>