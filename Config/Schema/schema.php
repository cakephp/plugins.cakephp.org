<?php
class AppSchema extends CakeSchema
{
    public function before($event = array())
    {
        $event;
        return true;
    }

    public function after($event = array())
    {
        $event;
    }

    public $maintainers = array(
        'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
        'user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'key' => 'index'),
        'group' => array('type' => 'string', 'null' => false, 'default' => 'maintainer', 'length' => 20, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'username' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'key' => 'unique', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'email' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'name' => array('type' => 'string', 'null' => true, 'length' => 50, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'alias' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'url' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'twitter_username' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 15, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'company' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'location' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
        'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
        'gravatar_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 32, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'password' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 40, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'activation_key' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 40, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'indexes' => array(
            'PRIMARY' => array('column' => 'id', 'unique' => 1),
            'username' => array('column' => 'username', 'unique' => 1),
            'name' => array('column' => 'name', 'unique' => 0),
            'activation_key' => array('column' => 'activation_key', 'unique' => 0),
            'user_id' => array('column' => 'user_id', 'unique' => 0)
        ),
        'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
    );

    public $packages = array(
        'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
        'maintainer_id' => array('type' => 'integer', 'null' => false, 'default' => null),
        'name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'repository_url' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'bakery_article' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'homepage' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'description' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'tags' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'category_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'open_issues' => array('type' => 'integer', 'null' => false, 'default' => '0'),
        'forks' => array('type' => 'integer', 'null' => false, 'default' => '0'),
        'watchers' => array('type' => 'integer', 'null' => false, 'default' => '0'),
        'collaborators' => array('type' => 'integer', 'null' => false, 'default' => '0'),
        'contributors' => array('type' => 'integer', 'null' => false, 'default' => '0'),
        'created_at' => array('type' => 'datetime', 'null' => true, 'default' => null),
        'last_pushed_at' => array('type' => 'datetime', 'null' => true, 'default' => null),
        'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
        'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
        'contains_model' => array('type' => 'boolean', 'null' => false, 'default' => null),
        'contains_view' => array('type' => 'boolean', 'null' => false, 'default' => null),
        'contains_controller' => array('type' => 'boolean', 'null' => false, 'default' => null),
        'contains_behavior' => array('type' => 'boolean', 'null' => false, 'default' => null),
        'contains_helper' => array('type' => 'boolean', 'null' => false, 'default' => null),
        'contains_component' => array('type' => 'boolean', 'null' => false, 'default' => null),
        'contains_shell' => array('type' => 'boolean', 'null' => false, 'default' => null),
        'contains_theme' => array('type' => 'boolean', 'null' => false, 'default' => null),
        'contains_datasource' => array('type' => 'boolean', 'null' => false, 'default' => null),
        'contains_vendor' => array('type' => 'boolean', 'null' => false, 'default' => null),
        'contains_test' => array('type' => 'boolean', 'null' => false, 'default' => null),
        'contains_lib' => array('type' => 'boolean', 'null' => false, 'default' => null),
        'contains_resource' => array('type' => 'boolean', 'null' => false, 'default' => null),
        'contains_config' => array('type' => 'boolean', 'null' => false, 'default' => null),
        'contains_app' => array('type' => 'boolean', 'null' => false, 'default' => null),
        'deleted' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'key' => 'index'),
        'indexes' => array(
            'PRIMARY' => array('column' => 'id', 'unique' => 1),
            'view' => array('column' => array('deleted', 'name', 'maintainer_id', 'category_id'), 'unique' => 0),
            'deleted' => array('column' => array('deleted', 'maintainer_id', 'last_pushed_at'), 'unique' => 0),
            'default_sort' => array('column' => array('deleted', 'created', 'maintainer_id'), 'unique' => 0)
        ),
        'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
    );

    public $user_details = array(
        'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'user_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'position' => array('type' => 'float', 'null' => false, 'default' => '1'),
        'field' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'value' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'input' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'data_type' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'label' => array('type' => 'string', 'null' => false, 'length' => 128, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
        'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
        'indexes' => array(
            'PRIMARY' => array('column' => 'id', 'unique' => 1),
            'UNIQUE_PROFILE_PROPERTY' => array('column' => array('field', 'user_id'), 'unique' => 1)
        ),
        'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
    );

    public $users = array(
        'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'username' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'slug' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'passwd' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'password_token' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'email' => array('type' => 'string', 'null' => true, 'default' => null, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'email_authenticated' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
        'email_token' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'email_token_expires' => array('type' => 'datetime', 'null' => true, 'default' => null),
        'tos' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
        'active' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
        'last_login' => array('type' => 'datetime', 'null' => true, 'default' => null),
        'last_activity' => array('type' => 'datetime', 'null' => true, 'default' => null),
        'is_admin' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
        'role' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
        'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
        'indexes' => array(
            'PRIMARY' => array('column' => 'id', 'unique' => 1),
            'BY_USERNAME' => array('column' => array('username', 'passwd'), 'unique' => 0),
            'BY_EMAIL' => array('column' => array('email', 'passwd'), 'unique' => 0)
        ),
        'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
    );
}
