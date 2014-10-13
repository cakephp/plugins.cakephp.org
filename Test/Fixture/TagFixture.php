<?php
/**
 * TagFixture
 */
class TagFixture extends CakeTestFixture {

/**
 * Name
 *
 * @var string $name
 */
	public $name = 'Tag';

/**
 * Table
 *
 * @var string $table
 */
	public $table = 'tags';

/**
 * Fields
 *
 * @var array $fields
 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
		'identifier' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 30, 'key' => 'index'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 30),
		'keyname' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 30),
		'occurrence' => array('type' => 'integer', 'null' => false, 'default' => 0, 'length' => 8),
		'article_occurrence' => array('type' => 'integer', 'null' => false, 'default' => 0, 'length' => 8),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'UNIQUE_TAG' => array('column' => array('identifier', 'keyname'), 'unique' => 1)
		)
	);

/**
 * Records
 *
 * @var array $records
 */
	public $records = array(
		array(
			'id'  => 'tag-1',
			'identifier'  => 'contains',
			'name'  => 'Model',
			'keyname'  => 'model',
			'occurrence' => 1,
			'created'  => '2008-06-02 18:18:11',
			'modified'  => '2008-06-02 18:18:37'
		),
		array(
			'id'  => 'tag-2',
			'identifier'  => 'contains',
			'name'  => 'theme',
			'keyname'  => 'theme',
			'occurrence' => 1,
			'created'  => '2008-06-01 18:18:15',
			'modified'  => '2008-06-01 18:18:15'
		),
	);

}
