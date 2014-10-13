<?php
class AddStarsToPackages extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = '';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_field' => array(
				'packages' => array(
					'forks_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => null, 'comment' => ''),
					'network_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => null, 'comment' => ''),
					'open_issues_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => null, 'comment' => ''),
					'stargazers_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => null, 'comment' => ''),
					'subscribers_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => null, 'comment' => ''),
					'watchers_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => null, 'comment' => ''),
				),
			),
		),
		'down' => array(
			'drop_field' => array(
				'packages' => array(
					'forks_count',
					'network_count',
					'open_issues_count',
					'stargazers_count',
					'subscribers_count',
					'watchers_count',
				),
			),
		),
	);

/**
 * Before migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return bool Should process continue
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return bool Should process continue
 */
	public function after($direction) {
		return true;
	}
}
