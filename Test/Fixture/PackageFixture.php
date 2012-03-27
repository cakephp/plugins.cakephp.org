<?php
/**
 * PackageFixture
 *
 */
class PackageFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'maintainer_id' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'repository_url' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'bakery_article' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'homepage' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'description' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'tags' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'category_id' => array('type' => 'integer', 'null' => true, 'default' => NULL),
		'open_issues' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'forks' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'watchers' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'collaborators' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'contributors' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'created_at' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'last_pushed_at' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'contains_model' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'contains_view' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'contains_controller' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'contains_behavior' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'contains_helper' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'contains_component' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'contains_shell' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'contains_theme' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'contains_datasource' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'contains_vendor' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'contains_test' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'contains_lib' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'contains_resource' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'contains_config' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'contains_app' => array('type' => 'boolean', 'null' => false, 'default' => NULL),
		'deleted' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'key' => 'index'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'view' => array('column' => array('deleted', 'name', 'maintainer_id', 'category_id'), 'unique' => 0), 'deleted' => array('column' => array('deleted', 'maintainer_id', 'last_pushed_at'), 'unique' => 0)),
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
			'maintainer_id' => 2,
			'name' => 'chocolate',
			'repository_url' => 'http://github.com/shama/chocolate',
			'bakery_article' => '',
			'homepage' => 'http://shama.github.com/chocolate/',
			'description' => 'Lorem ipsum dolor sit amet',
			'tags' => 'Lorem ipsum dolor sit amet',
			'category_id' => 1,
			'open_issues' => 1,
			'forks' => 10,
			'watchers' => 10,
			'collaborators' => 1,
			'contributors' => 1,
			'created_at' => '2012-02-24 04:42:44',
			'last_pushed_at' => '2012-02-24 04:42:44',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44',
			'contains_model' => 1,
			'contains_view' => 1,
			'contains_controller' => 1,
			'contains_behavior' => 1,
			'contains_helper' => 1,
			'contains_component' => 1,
			'contains_shell' => 1,
			'contains_theme' => 1,
			'contains_datasource' => 1,
			'contains_vendor' => 1,
			'contains_test' => 1,
			'contains_lib' => 1,
			'contains_resource' => 1,
			'contains_config' => 1,
			'contains_app' => 1,
			'deleted' => 0,
		),
		array(
			'id' => 2,
			'maintainer_id' => 2,
			'name' => 'peanutbutter',
			'repository_url' => 'http://github.com/shama/peanutbutter',
			'bakery_article' => '',
			'homepage' => 'http://shama.github.com/peanutbutter/',
			'description' => 'Lorem ipsum dolor sit amet',
			'tags' => 'Lorem ipsum dolor sit amet',
			'category_id' => 2,
			'open_issues' => 1,
			'forks' => 1,
			'watchers' => 1,
			'collaborators' => 1,
			'contributors' => 1,
			'created_at' => '2012-02-24 04:42:44',
			'last_pushed_at' => '2012-02-24 04:42:44',
			'created' => '2012-02-24 04:42:44',
			'modified' => '2012-02-24 04:42:44',
			'contains_model' => 1,
			'contains_view' => 0,
			'contains_controller' => 0,
			'contains_behavior' => 0,
			'contains_helper' => 0,
			'contains_component' => 0,
			'contains_shell' => 0,
			'contains_theme' => 0,
			'contains_datasource' => 0,
			'contains_vendor' => 0,
			'contains_test' => 0,
			'contains_lib' => 0,
			'contains_resource' => 0,
			'contains_config' => 0,
			'contains_app' => 0,
			'deleted' => 0,
		),
	);
}
