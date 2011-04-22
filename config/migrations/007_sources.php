<?php
class M4db126f0bbe4472f9d762e7ecbdd56cb extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 * @access public
 */
	public $description = '';

/**
 * Actions to be performed
 *
 * @var array $migration
 * @access public
 */
	public $migration = array(
		'up' => array(
			'create_table' => array(
				'sources' => array(
					'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
					'package_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
					'type' => array('type' => 'string', 'null' => false, 'default' => 'git', 'length' => 16, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'name' => array('type' => 'string', 'null' => false, 'default' => 'github', 'length' => 32, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'path' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'default' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
					'deleted' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
					'official' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
					'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
					'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
						'type' => array('column' => 'type', 'unique' => 0),
						'name' => array('column' => 'name', 'unique' => 0),
						'package_type' => array('column' => array('package_id', 'type'), 'unique' => 0),
						'package_type_default' => array('column' => array('package_id', 'type', 'default'), 'unique' => 0),
					),
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB'),
				),
			),
		),
		'down' => array(
			'drop_table' => array(
				'sources'
			),
		),
	);

/**
 * Before migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 * @access public
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 * @access public
 */
	public function after($direction) {
		return true;
	}
}
?>