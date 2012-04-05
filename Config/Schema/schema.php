<?php 
/* generated on: 2012-01-17 01:34:02 : 1326764042 */
class AppSchema extends CakeSchema {

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $maintainers = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary', 'collate' => NULL, 'comment' => ''),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'key' => 'index', 'collate' => NULL, 'comment' => ''),
		'group' => array('type' => 'string', 'null' => false, 'default' => 'maintainer', 'length' => 20, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'username' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 50, 'key' => 'unique', 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'email' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'name' => array('type' => 'string', 'null' => true, 'length' => 50, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'alias' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'url' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'twitter_username' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 15, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'company' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'location' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'gravatar_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'password' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 40, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'activation_key' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 40, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'username' => array('column' => 'username', 'unique' => 1), 'name' => array('column' => 'name', 'unique' => 0), 'activation_key' => array('column' => 'activation_key', 'unique' => 0), 'user_id' => array('column' => 'user_id', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	public $packages = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary', 'collate' => NULL, 'comment' => ''),
		'maintainer_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'repository_url' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'bakery_article' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'homepage' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'description' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'tags' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'category_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36, 'collate' => NULL, 'comment' => ''),
		'open_issues' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => NULL, 'comment' => ''),
		'forks' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => NULL, 'comment' => ''),
		'watchers' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => NULL, 'comment' => ''),
		'collaborators' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => NULL, 'comment' => ''),
		'contributors' => array('type' => 'integer', 'null' => false, 'default' => '0', 'collate' => NULL, 'comment' => ''),
		'created_at' => array('type' => 'datetime', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'last_pushed_at' => array('type' => 'datetime', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'contains_model' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'contains_view' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'contains_controller' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'contains_behavior' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'contains_helper' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'contains_component' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'contains_shell' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'contains_theme' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'contains_datasource' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'contains_vendor' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'contains_test' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'contains_lib' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'contains_resource' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'contains_config' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'contains_app' => array('type' => 'boolean', 'null' => false, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'deleted' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'key' => 'index', 'collate' => NULL, 'comment' => ''),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'view' => array('column' => array('deleted', 'name', 'maintainer_id', 'category_id'), 'unique' => 0), 'deleted' => array('column' => array('deleted', 'maintainer_id', 'last_pushed_at'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	public $user_details = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'user_id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'position' => array('type' => 'float', 'null' => false, 'default' => '1', 'collate' => NULL, 'comment' => ''),
		'field' => array('type' => 'string', 'null' => false, 'default' => NULL, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'value' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'input' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'data_type' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'label' => array('type' => 'string', 'null' => false, 'length' => 128, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'UNIQUE_PROFILE_PROPERTY' => array('column' => array('field', 'user_id'), 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
	public $users = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'username' => array('type' => 'string', 'null' => false, 'default' => NULL, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'slug' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'passwd' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 128, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'password_token' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 128, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'email' => array('type' => 'string', 'null' => true, 'default' => NULL, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'email_authenticated' => array('type' => 'boolean', 'null' => true, 'default' => '0', 'collate' => NULL, 'comment' => ''),
		'email_token' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'email_token_expires' => array('type' => 'datetime', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'tos' => array('type' => 'boolean', 'null' => true, 'default' => '0', 'collate' => NULL, 'comment' => ''),
		'active' => array('type' => 'boolean', 'null' => true, 'default' => '0', 'collate' => NULL, 'comment' => ''),
		'last_login' => array('type' => 'datetime', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'last_activity' => array('type' => 'datetime', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'is_admin' => array('type' => 'boolean', 'null' => true, 'default' => '0', 'collate' => NULL, 'comment' => ''),
		'role' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'comment' => '', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL, 'collate' => NULL, 'comment' => ''),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'BY_USERNAME' => array('column' => array('username', 'passwd'), 'unique' => 0), 'BY_EMAIL' => array('column' => array('email', 'passwd'), 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
