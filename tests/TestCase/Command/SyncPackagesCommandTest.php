<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\SyncPackagesCommand;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\I18n\Date;
use Cake\TestSuite\TestCase;
use GuzzleHttp\Client;
use Packagist\Api\Result\Package\Version;
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

        $this->assertTrue($method->invoke($command, ['PHP: 8.2', 'CakePHP: 5.0']));
        $this->assertFalse($method->invoke($command, ['PHP: 8.2']));
    }

    /**
     * @return void
     */
    public function testExtractReleaseDate(): void
    {
        $command = new SyncPackagesCommand();
        $method = new ReflectionMethod($command, 'extractReleaseDate');

        $version = new Version();
        $version->fromArray([
            'time' => '2026-04-05T11:22:33+00:00',
        ]);

        $this->assertEquals(new Date('2026-04-05'), $method->invoke($command, $version));
        $this->assertNull($method->invoke($command, null));
    }

    /**
     * @return void
     */
    public function testCreatePackagistHttpClientUsesApiBestPractices(): void
    {
        $userAgent = 'plugins.cakephp.org-test (mailto=test@example.com)';
        $previousUserAgent = $_ENV['PACKAGIST_USER_AGENT'] ?? null;
        $_ENV['PACKAGIST_USER_AGENT'] = $userAgent;

        try {
            $command = new SyncPackagesCommand();
            $method = new ReflectionMethod($command, 'createPackagistHttpClient');

            /** @var \GuzzleHttp\Client $client */
            $client = $method->invoke($command);
        } finally {
            if ($previousUserAgent === null) {
                unset($_ENV['PACKAGIST_USER_AGENT']);
            } else {
                $_ENV['PACKAGIST_USER_AGENT'] = $previousUserAgent;
            }
        }

        $this->assertInstanceOf(Client::class, $client);
        $this->assertSame(2.0, $client->getConfig('version'));
        $this->assertSame($userAgent, $client->getConfig('headers')['User-Agent']);
        $this->assertStringContainsString('mailto=', $client->getConfig('headers')['User-Agent']);
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
