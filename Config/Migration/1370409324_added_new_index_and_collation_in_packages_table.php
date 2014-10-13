<?php
class AddedNewIndexAndCollationInPackagesTable extends CakeMigration {

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
			'alter_field' => array(
				'packages' => array(
					'category_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
				),
			),
			'create_field' => array(
				'packages' => array(
					'indexes' => array(
						'default_sort' => array('column' => array('deleted', 'created', 'maintainer_id'), 'unique' => 0),
					),
				),
			),
		),
		'down' => array(
			'alter_field' => array(
				'packages' => array(
					'category_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => null, 'comment' => ''),
				),
			),
			'drop_field' => array(
				'packages' => array('', 'indexes' => array('default_sort')),
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
