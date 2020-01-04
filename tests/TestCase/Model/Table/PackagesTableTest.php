<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PackagesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PackagesTable Test Case
 */
class PackagesTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\PackagesTable
     */
    public $Packages;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.packages',
        'app.maintainers',
        'app.categories',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('Packages') ? [] : ['className' => 'App\Model\Table\PackagesTable'];
        $this->Packages = TableRegistry::get('Packages', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Packages);

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
