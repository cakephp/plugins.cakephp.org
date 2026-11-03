<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\SyncPackagesCommand;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Http\Client;
use Cake\Http\Client\ClientEvent;
use Cake\Http\Client\Response;
use Cake\I18n\Date;
use Cake\TestSuite\TestCase;
use Packagist\Api\Client as PackagistClient;
use Packagist\Api\Result\Package\Version;
use PHPUnit\Framework\Attributes\DataProvider;
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
    #[DataProvider('userAgentProvider')]
    public function testCreatePackagistHttpClientUsesApiBestPractices(?string $configuredUserAgent, string $userAgent): void
    {
        $previousUserAgent = $_ENV['PACKAGIST_USER_AGENT'] ?? null;
        unset($_ENV['PACKAGIST_USER_AGENT']);
        if ($configuredUserAgent !== null) {
            $_ENV['PACKAGIST_USER_AGENT'] = $configuredUserAgent;
        }

        try {
            $command = new SyncPackagesCommand();
            $method = new ReflectionMethod($command, 'createPackagistHttpClient');

            /** @var \Cake\Http\Client $client */
            $client = $method->invoke($command);
        } finally {
            if ($previousUserAgent === null) {
                unset($_ENV['PACKAGIST_USER_AGENT']);
            } else {
                $_ENV['PACKAGIST_USER_AGENT'] = $previousUserAgent;
            }
        }

        $this->assertInstanceOf(Client::class, $client);
        $client->getEventManager()->on('HttpClient.beforeSend', function (ClientEvent $event) use ($userAgent): void {
            $request = $event->getRequest();
            $this->assertSame('2', $request->getProtocolVersion());
            $this->assertSame($userAgent, $request->getHeaderLine('User-Agent'));
            $this->assertSame('https://packagist.org/packages/list.json?type=cakephp-plugin', (string)$request->getUri());
            $event->setResult(new Response(
                ['HTTP/2 200 OK', 'Content-Type: application/json'],
                '{"packageNames":["cakephp/authentication"]}',
            ));
        });

        $packagist = new PackagistClient($client);
        $this->assertSame(['cakephp/authentication'], $packagist->all(['type' => 'cakephp-plugin']));
    }

    /**
     * @return array<string, array{?string, string}>
     */
    public static function userAgentProvider(): array
    {
        $custom = 'plugins.cakephp.org-test (mailto=test@example.com)';

        return [
            'default' => [null, 'plugins.cakephp.org (https://plugins.cakephp.org; mailto=security@cakephp.org)'],
            'custom' => [$custom, $custom],
        ];
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
