<?php
class M4f14cc59b43c4d8d9f7ba1fe75f6eb26 extends CakeMigration {

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
			'alter_field' => array(
				'seos' => array(
					'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'collate' => null, 'comment' => ''),
					'slug' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'unique', 'collate' => 'utf8_general_ci', 'charset' => 'utf8', 'comment' => ''),
					'spec' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8', 'key' => 'unique', 'comment' => ''),
					'title_for_layout' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8', 'comment' => ''),
					'description' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8', 'comment' => ''),
					'keywords' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8', 'comment' => ''),
					'canonical' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8', 'comment' => ''),
					'h2_for_layout' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8', 'comment' => ''),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'collate' => null, 'comment' => ''),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'collate' => null, 'comment' => ''),
				),
			),
			'drop_field' => array(
				'seos' => array('', 'indexes' => array('slug_spec')),
			),
			'create_field' => array(
				'seos' => array(
					'indexes' => array(
						'slug' => array('column' => 'slug', 'unique' => 1),
						'spec' => array('column' => 'spec', 'unique' => 1),
					),
				),
			),
		),
		'down' => array(
			'alter_field' => array(
				'seos' => array(
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
				),
			),
			'create_field' => array(
				'seos' => array(
					'indexes' => array(
						'slug_spec' => array('column' => array('slug', 'spec'), 'unique' => 1),
					),
				),
			),
			'drop_field' => array(
				'seos' => array('', 'indexes' => array('slug', 'spec')),
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
