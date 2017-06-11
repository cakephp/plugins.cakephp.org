<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TaggedFixture
 *
 */
class TaggedFixture extends TestFixture
{

    /**
     * Table name
     *
     * @var string
     */
    public $table = 'tagged';

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'string', 'length' => 36, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'foreign_key' => ['type' => 'string', 'length' => 36, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'tag_id' => ['type' => 'string', 'length' => 36, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'model' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'language' => ['type' => 'string', 'length' => 6, 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'times_tagged' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        '_indexes' => [
            'INDEX_TAGGED' => ['type' => 'index', 'columns' => ['model'], 'length' => []],
            'INDEX_LANGUAGE' => ['type' => 'index', 'columns' => ['language'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'UNIQUE_TAGGING' => ['type' => 'unique', 'columns' => ['model', 'foreign_key', 'tag_id', 'language'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'MyISAM',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => '529502f0-422d-4b88-998c-2baaf54f5a9f',
            'foreign_key' => 'Lorem ipsum dolor sit amet',
            'tag_id' => 'Lorem ipsum dolor sit amet',
            'model' => 'Lorem ipsum dolor sit amet',
            'language' => 'Lore',
            'created' => '2017-04-10 00:43:41',
            'modified' => '2017-04-10 00:43:41',
            'times_tagged' => 1
        ],
    ];
}
