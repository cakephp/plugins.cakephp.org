<?php
class M4da202c0fc984da39bdb59a0cbdd56cb extends CakeMigration {

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
				'blog_posts' => array(
					'tableParameters' => array('engine' => 'InnoDB'),
				),
				'maintainers' => array(
					'tableParameters' => array('engine' => 'InnoDB'),
				),
				'packages' => array(
					'tableParameters' => array('engine' => 'InnoDB'),
				),
				'packages_tags' => array(
					'tableParameters' => array('engine' => 'InnoDB'),
				),
				'settings' => array(
					'tableParameters' => array('engine' => 'InnoDB'),
				),
				'tags' => array(
					'tableParameters' => array('engine' => 'InnoDB'),
				),
			),
		),
		'down' => array(
			'alter_field' => array(
				'blog_posts' => array(
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM'),
				),
				'maintainers' => array(
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM'),
				),
				'packages' => array(
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM'),
				),
				'packages_tags' => array(
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM'),
				),
				'settings' => array(
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM'),
				),
				'tags' => array(
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM'),
				),
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