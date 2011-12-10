<?php
class TagFixture extends CakeTestFixture {

    var $name = 'Tag';

    var $fields = array(
        'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
        'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 40, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'packages_count' => array('type' => 'integer', 'null' => false, 'default' => '0'),
        'lft' => array('type' => 'integer', 'null' => true, 'default' => NULL),
        'rght' => array('type' => 'integer', 'null' => true, 'default' => NULL),
        'parent_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
        'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'name' => array('column' => 'name', 'unique' => 0)),
        'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
    );

    var $records = array(
        array(
            'id' => 1,
            'name' => 'Lorem ipsum dolor sit amet',
            'packages_count' => 1,
            'lft' => 1,
            'rght' => 1,
            'parent_id' => 1
        ),
    );

}