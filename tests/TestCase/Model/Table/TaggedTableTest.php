<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TaggedTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TaggedTable Test Case
 */
class TaggedTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\TaggedTable
     */
    public $Tagged;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.tagged',
        'app.tags'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Tagged') ? [] : ['className' => 'App\Model\Table\TaggedTable'];
        $this->Tagged = TableRegistry::get('Tagged', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Tagged);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
