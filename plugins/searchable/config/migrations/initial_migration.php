<?php
class M4b99be1fadb44683b662c4d6cbdd56cb extends CakeMigration {

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
				'search_index' => array(
					'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
					'model' => array('type' => 'string', 'null' => false, 'default' => NULL, 'key' => 'index'),
					'foreign_key' => array('type' => 'integer', 'null' => false, 'default' => NULL),
					'data' => array('type' => 'text', 'null' => false, 'default' => NULL, 'key' => 'index'),
					'name' => array('type' => 'string', 'null' => true, 'default' => NULL),
					'summary' => array('type' => 'text', 'null' => true, 'default' => NULL),
					'url' => array('type' => 'text', 'null' => true, 'default' => NULL),
					'active' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'key' => 'index'),
					'published' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
						'model' => array('column' => array('model', 'foreign_key'), 'unique' => 1),
						'active' => array('column' => 'active', 'unique' => 0),
						'data' => array('column' => 'data', 'unique' => 0),
					),
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM'),
				),
				'settings' => array(
					'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
					'key' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 64, 'key' => 'unique'),
					'value' => array('type' => 'string', 'null' => false, 'default' => NULL),
					'title' => array('type' => 'string', 'null' => false, 'default' => NULL),
					'description' => array('type' => 'string', 'null' => false, 'default' => NULL),
					'input_type' => array('type' => 'string', 'null' => false, 'default' => 'text'),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
						'key' => array('column' => 'key', 'unique' => 1),
					),
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM'),
				),
			),
		),
		'down' => array(
			'drop_table' => array(
				'search_index', 'settings'
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