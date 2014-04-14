<?php
class AddStarsToPackages extends CakeMigration {

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
                    'forks_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => NULL, 'comment' => ''),
                    'network_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => NULL, 'comment' => ''),
                    'open_issues_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => NULL, 'comment' => ''),
                    'stargazers_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => NULL, 'comment' => ''),
                    'subscribers_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => NULL, 'comment' => ''),
                    'watchers_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => NULL, 'comment' => ''),
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
