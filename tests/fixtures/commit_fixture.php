<?php
class CommitFixture extends CakeTestFixture {

    var $name = 'Commit';

    var $fields = array(
        'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
        'package_id' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'key' => 'index'),
        'commit_hash' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 40, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'author_details' => array('type' => 'string', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'commit_msg' => array('type' => 'integer', 'null' => true, 'default' => NULL),
        'commit_details' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'commit_date' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
        'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
        'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
        'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'package_id' => array('column' => 'package_id', 'unique' => 0)),
        'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
    );

    var $records = array(
        array(
            'id' => 1,
            'package_id' => 1,
            'commit_hash' => 'Lorem ipsum dolor sit amet',
            'author_details' => 'Lorem ipsum dolor sit amet',
            'commit_msg' => 1,
            'commit_details' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'commit_date' => '2011-06-04 18:11:36',
            'created' => '2011-06-04 18:11:36',
            'modified' => '2011-06-04 18:11:36'
        ),
    );

}