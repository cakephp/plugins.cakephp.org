<?php
class AddGithubStuffToMaintainers extends CakeMigration {

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
				'maintainers' => array(
					'github_id' => array('type' => 'integer', 'null' => false, 'default' => null),
					'avatar_url' => array('type' => 'string', 'null' => false, 'default' => null),
				),
			),
		),
		'down' => array(
			'drop_field' => array(
				'maintainers' => array('github_id', 'avatar_url',),
			),
		),
	);

/**
 * Before migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 */
	public function after($direction) {
		return true;
	}
}
