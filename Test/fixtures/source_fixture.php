<?php
class SourceFixture extends CakeTestFixture {

    var $name = 'Source';

    var $fields = array(
        'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
        'package_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
        'type' => array('type' => 'string', 'null' => false, 'default' => 'git', 'length' => 16, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'name' => array('type' => 'string', 'null' => false, 'default' => 'github', 'length' => 32, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'path' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'default' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
        'deleted' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
        'official' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
        'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
        'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
        'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'type' => array('column' => 'type', 'unique' => 0), 'name' => array('column' => 'name', 'unique' => 0), 'package_type' => array('column' => array('package_id', 'type'), 'unique' => 0), 'package_type_default' => array('column' => array('package_id', 'type', 'default'), 'unique' => 0)),
        'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
    );

    var $records = array(
        array(
            'id' => 1,
            'package_id' => 1,
            'type' => 'Lorem ipsum do',
            'name' => 'Lorem ipsum dolor sit amet',
            'path' => 'Lorem ipsum dolor sit amet',
            'default' => 1,
            'deleted' => 1,
            'official' => 1,
            'created' => '2011-06-04 18:11:38',
            'modified' => '2011-06-04 18:11:38',
            'indexes' => '2011-06-04 18:11:38'
        ),
    );

}