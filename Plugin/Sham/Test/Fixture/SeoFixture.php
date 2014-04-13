<?php
/**
 * CakePHP Sham Plugin
 *
 * Seo fixture
 *
 * @package 	Sham
 * @subpackage 	Sham.Test.Fixture
 */
class SeoFixture extends CakeTestFixture {

/**
 * name property
 *
 * @var string 'Seo'
 */
	public $name = 'Seo';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'slug' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'spec' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'title_for_layout' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'description' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'keywords' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'canonical' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'h2_for_layout' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'slug_spec' => array('column' => array('slug', 'spec'), 'unique' => 1)),
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
			'slug' => 'Lorem ipsum dolor sit amet',
			'spec' => 'Lorem ipsum dolor sit amet',
			'title_for_layout' => 'Lorem ipsum dolor sit amet',
			'description' => 'Lorem ipsum dolor sit amet',
			'keywords' => 'Lorem ipsum dolor sit amet',
			'canonical' => 'Lorem ipsum dolor sit amet',
			'h2_for_layout' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44'
		),
		array(
			'id' => 2,
			'slug' => 'Lorem ipsum dolor sit amet',
			'spec' => 'Lorem ipsum dolor sit amet',
			'title_for_layout' => 'Lorem ipsum dolor sit amet',
			'description' => 'Lorem ipsum dolor sit amet',
			'keywords' => 'Lorem ipsum dolor sit amet',
			'canonical' => 'Lorem ipsum dolor sit amet',
			'h2_for_layout' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44'
		),
		array(
			'id' => 3,
			'slug' => 'Lorem ipsum dolor sit amet',
			'spec' => 'Lorem ipsum dolor sit amet',
			'title_for_layout' => 'Lorem ipsum dolor sit amet',
			'description' => 'Lorem ipsum dolor sit amet',
			'keywords' => 'Lorem ipsum dolor sit amet',
			'canonical' => 'Lorem ipsum dolor sit amet',
			'h2_for_layout' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44'
		),
		array(
			'id' => 4,
			'slug' => 'Lorem ipsum dolor sit amet',
			'spec' => 'Lorem ipsum dolor sit amet',
			'title_for_layout' => 'Lorem ipsum dolor sit amet',
			'description' => 'Lorem ipsum dolor sit amet',
			'keywords' => 'Lorem ipsum dolor sit amet',
			'canonical' => 'Lorem ipsum dolor sit amet',
			'h2_for_layout' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44'
		),
		array(
			'id' => 5,
			'slug' => 'Lorem ipsum dolor sit amet',
			'spec' => 'Lorem ipsum dolor sit amet',
			'title_for_layout' => 'Lorem ipsum dolor sit amet',
			'description' => 'Lorem ipsum dolor sit amet',
			'keywords' => 'Lorem ipsum dolor sit amet',
			'canonical' => 'Lorem ipsum dolor sit amet',
			'h2_for_layout' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44'
		),
		array(
			'id' => 6,
			'slug' => 'Lorem ipsum dolor sit amet',
			'spec' => 'Lorem ipsum dolor sit amet',
			'title_for_layout' => 'Lorem ipsum dolor sit amet',
			'description' => 'Lorem ipsum dolor sit amet',
			'keywords' => 'Lorem ipsum dolor sit amet',
			'canonical' => 'Lorem ipsum dolor sit amet',
			'h2_for_layout' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44'
		),
		array(
			'id' => 7,
			'slug' => 'Lorem ipsum dolor sit amet',
			'spec' => 'Lorem ipsum dolor sit amet',
			'title_for_layout' => 'Lorem ipsum dolor sit amet',
			'description' => 'Lorem ipsum dolor sit amet',
			'keywords' => 'Lorem ipsum dolor sit amet',
			'canonical' => 'Lorem ipsum dolor sit amet',
			'h2_for_layout' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44'
		),
		array(
			'id' => 8,
			'slug' => 'Lorem ipsum dolor sit amet',
			'spec' => 'Lorem ipsum dolor sit amet',
			'title_for_layout' => 'Lorem ipsum dolor sit amet',
			'description' => 'Lorem ipsum dolor sit amet',
			'keywords' => 'Lorem ipsum dolor sit amet',
			'canonical' => 'Lorem ipsum dolor sit amet',
			'h2_for_layout' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44'
		),
		array(
			'id' => 9,
			'slug' => 'Lorem ipsum dolor sit amet',
			'spec' => 'Lorem ipsum dolor sit amet',
			'title_for_layout' => 'Lorem ipsum dolor sit amet',
			'description' => 'Lorem ipsum dolor sit amet',
			'keywords' => 'Lorem ipsum dolor sit amet',
			'canonical' => 'Lorem ipsum dolor sit amet',
			'h2_for_layout' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44'
		),
		array(
			'id' => 10,
			'slug' => 'Lorem ipsum dolor sit amet',
			'spec' => 'Lorem ipsum dolor sit amet',
			'title_for_layout' => 'Lorem ipsum dolor sit amet',
			'description' => 'Lorem ipsum dolor sit amet',
			'keywords' => 'Lorem ipsum dolor sit amet',
			'canonical' => 'Lorem ipsum dolor sit amet',
			'h2_for_layout' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44'
		),
	);

}
