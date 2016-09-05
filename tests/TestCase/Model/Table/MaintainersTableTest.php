<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MaintainersTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MaintainersTable Test Case
 */
class MaintainersTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\MaintainersTable
     */
    public $Maintainers;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.maintainers',
        'app.users',
        'app.gravatars',
        'app.githubs',
        'app.packages',
        'app.categories'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Maintainers') ? [] : ['className' => 'App\Model\Table\MaintainersTable'];
        $this->Maintainers = TableRegistry::get('Maintainers', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Maintainers);

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
