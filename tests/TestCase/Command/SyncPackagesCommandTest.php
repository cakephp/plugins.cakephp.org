<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\SyncPackagesCommand;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use ReflectionMethod;

/**
 * App\Command\SyncPackagesCommand Test Case
 *
 * @link \App\Command\SyncPackagesCommand
 */
class SyncPackagesCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * @return void
     */
    public function testHasExplicitCakePhpDependency(): void
    {
        $command = new SyncPackagesCommand();
        $method = new ReflectionMethod($command, 'hasExplicitCakePhpDependency');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($command, ['PHP: 8.2', 'CakePHP: 5.0']));
        $this->assertFalse($method->invoke($command, ['PHP: 8.2']));
    }

    /**
     * Test defaultName method
     *
     * @return void
     * @link \App\Command\SyncPackagesCommand::defaultName()
     */
    public function testDefaultName(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getDescription method
     *
     * @return void
     * @link \App\Command\SyncPackagesCommand::getDescription()
     */
    public function testGetDescription(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildOptionParser method
     *
     * @return void
     * @link \App\Command\SyncPackagesCommand::buildOptionParser()
     */
    public function testBuildOptionParser(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test execute method
     *
     * @return void
     * @link \App\Command\SyncPackagesCommand::execute()
     */
    public function testExecute(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
