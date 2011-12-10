<?php
class LogFixture extends CakeTestFixture {

    var $name = 'Log';

    var $fields = array(
        'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'type' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 100, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'message' => array('type' => 'string', 'null' => false, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
        'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'type' => array('column' => 'type', 'unique' => 0)),
        'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
    );

    var $records = array(
        array(
            'id' => '4dea7559-bec0-4ee3-a5e0-4782cbdd56cb',
            'type' => 'Lorem ipsum dolor sit amet',
            'message' => 'Lorem ipsum dolor sit amet',
            'created' => '2011-06-04 18:11:37',
            'indexes' => '2011-06-04 18:11:37'
        ),
    );

}