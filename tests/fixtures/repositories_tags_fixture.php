<?php
/* PackagesTags Fixture generated on: 2010-02-11 06:02:55 : 1265886415 */
class PackagesTagsFixture extends CakeTestFixture {
	var $name = 'PackagesTags';

	var $fields = array(
		'package_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'tag_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'indexes' => array('package_id' => array('column' => 'package_id', 'unique' => 0), 'tag_id' => array('column' => 'tag_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'MyISAM')
	);

	var $records = array(
		array(
			'package_id' => 1,
			'tag_id' => 1
		),
	);
}
?>