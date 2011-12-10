<?php
class M4e9e3bde9b604c4496f83dfacbdd56cb extends CakeMigration {

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
				'profiles' => array(
					'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
					'user_id' => array('type' => 'string', 'null' => false, 'default' => '', 'length' => 36, 'key' => 'unique'),
					'rating' => array('type' => 'float', 'null' => false, 'default' => '0', 'length' => '10,2'),
					'rating_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
					'rating_sum' => array('type' => 'float', 'null' => false, 'default' => '0', 'length' => '10,2'),
					'published' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'key' => 'index'),
					'producer' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'key' => 'index'),
					'location' => array('type' => 'string', 'null' => true, 'default' => NULL),
					'interests' => array('type' => 'string', 'null' => true, 'default' => NULL),
					'occupation' => array('type' => 'string', 'null' => true, 'default' => NULL),
					'icq' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 20),
					'aim' => array('type' => 'string', 'null' => true, 'default' => NULL),
					'yahoo' => array('type' => 'string', 'null' => true, 'default' => NULL),
					'msnm' => array('type' => 'string', 'null' => true, 'default' => NULL),
					'jabber' => array('type' => 'string', 'null' => true, 'default' => NULL),
					'time_zone' => array('type' => 'string', 'null' => true, 'default' => NULL),
					'birthday' => array('type' => 'date', 'null' => true, 'default' => NULL),
					'user_icon' => array('type' => 'string', 'null' => true, 'default' => NULL),
					'signature' => array('type' => 'text', 'null' => true, 'default' => NULL),
					'url' => array('type' => 'string', 'null' => true, 'default' => NULL),
					'bio' => array('type' => 'text', 'null' => true, 'default' => NULL),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
						'USER_ID_UNIQUE_INDEX' => array('column' => 'user_id', 'unique' => 1),
						'published' => array('column' => 'published', 'unique' => 0),
					),
				),
			),
		),
		'down' => array(
			'drop_table' => array(
				'profiles'),
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