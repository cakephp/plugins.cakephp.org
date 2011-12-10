<?php
class SchemaMigrationFixture extends CakeTestFixture {

    var $name = 'SchemaMigration';

    var $fields = array(
        'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
        'version' => array('type' => 'integer', 'null' => false, 'default' => NULL),
        'type' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 50, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
        'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
        'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
        'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'MyISAM')
    );

    var $records = array(
        array(
            'id' => 1,
            'version' => 1,
            'type' => 'Lorem ipsum dolor sit amet',
            'created' => '2011-06-04 18:11:37'
        ),
    );

}