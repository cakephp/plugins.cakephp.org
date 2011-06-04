<?php
class JobFixture extends CakeTestFixture {

    var $name = 'Job';

    var $fields = array(
        'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
        'handler' => array('type' => 'text', 'null' => false, 'default' => NULL, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
        'queue' => array('type' => 'string', 'null' => false, 'default' => 'default', 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
        'attempts' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
        'run_at' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
        'locked_at' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
        'locked_by' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
        'failed_at' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
        'error' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'latin1_swedish_ci', 'charset' => 'latin1'),
        'created_at' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
        'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
        'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'InnoDB')
    );

    var $records = array(
        array(
            'id' => 1,
            'handler' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'queue' => 'Lorem ipsum dolor sit amet',
            'attempts' => 1,
            'run_at' => '2011-06-04 18:11:36',
            'locked_at' => '2011-06-04 18:11:36',
            'locked_by' => 'Lorem ipsum dolor sit amet',
            'failed_at' => '2011-06-04 18:11:36',
            'error' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'created_at' => '2011-06-04 18:11:36'
        ),
    );

}