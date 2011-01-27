<?php
class M4d4100e680cc4348a7422ac5cbdd56cb extends CakeMigration {

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
			'create_field' => array(
				'packages' => array(
					'open_issues' => array('type' => 'integer', 'null' => false, 'default' => '0'),
					'forks' => array('type' => 'integer', 'null' => false, 'default' => '0'),
					'watchers' => array('type' => 'integer', 'null' => false, 'default' => '0'),
					'contributors' => array('type' => 'integer', 'null' => false, 'default' => '0'),
					'collaborators' => array('type' => 'integer', 'null' => false, 'default' => '0'),
					'created_at' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
					'last_pushed_at' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
				),
			),
			'drop_table' => array(
			),
		),
		'down' => array(
			'drop_field' => array(
				'packages' => array('open_issues', 'forks', 'watchers', 'contributors', 'collaborators', 'created_at', 'last_pushed_at',),
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