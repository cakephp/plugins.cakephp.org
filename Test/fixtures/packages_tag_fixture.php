<?php
class PackagesTagFixture extends CakeTestFixture {

    var $name = 'PackagesTag';

    var $fields = array(
        'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
        'package_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
        'tag_id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'index'),
        'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'tag_id' => array('column' => 'tag_id', 'unique' => 0), 'package_id' => array('column' => 'package_id', 'unique' => 0)),
        'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
    );

    var $records = array(
        array(
            'id' => 1,
            'package_id' => 1,
            'tag_id' => 1
        ),
    );

}