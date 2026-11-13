<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Application;
use App\Model\Table\PackagesTable;
use App\Service\PackagistImporter;
use Cake\Core\Container;
use Cake\Http\Client;
use Cake\Http\Client\ClientEvent;
use Cake\Http\Client\Response;
use Cake\I18n\Date;
use Cake\TestSuite\TestCase;
use Override;
use Packagist\Api\Client as PackagistClient;
use Packagist\Api\Result\Package;
use Packagist\Api\Result\Package\Downloads;
use Packagist\Api\Result\Package\Version;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use UnexpectedValueException;

/**
 * App\Service\PackagistImporter Test Case
 *
 * @link \App\Service\PackagistImporter
 */
class PackagistImporterTest extends TestCase
{
    /**
     * @var list<string>
     */
    #[Override]
    protected array $fixtures = ['app.Packages', 'app.Categories', 'app.CategoriesPackages', 'plugin.Tags.Tags', 'plugin.Tags.Tagged'];

    /**
     * Package metadata and version tags refresh without losing curation.
     */
    public function testSyncUpdatesMetadataAndPreservesCategories(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $version = new Version();
        $version->fromArray(['version' => '5.1.0', 'time' => '2026-04-05T11:22:33+00:00', 'require' => ['php' => '^8.2', 'cakephp/cakephp' => '^5.0']]);
        $downloads = new Downloads();
        $downloads->fromArray([
            'total' => 12345,
            'monthly' => 1234,
        ]);
        $metadata = new Package();
        $metadata->fromArray([
            'description' => 'Updated plugin description', 'repository' => 'https://github.com/markstory/asset_compress',
            'downloads' => $downloads, 'github_stars' => 500, 'versions' => [$version],
        ]);
        $client = $this->createMock(PackagistClient::class);
        $client->expects($this->once())->method('all')->with(['type' => 'cakephp-plugin'])->willReturn(['markstory/asset_compress']);
        $client->expects($this->once())->method('get')->with('markstory/asset_compress')->willReturn($metadata);
        $progress = [];
        $result = new PackagistImporter($packages, $client)->sync(static function (string $name, int $current, int $total) use (&$progress): void {
            $progress[] = [$name, $current, $total];
        });
        $this->assertSame(['total' => 1, 'saved' => 1, 'skipped' => 0, 'failed' => 0, 'deleted' => 25, 'deleteFailed' => 0], $result);
        $this->assertSame([['markstory/asset_compress', 1, 1]], $progress);
        $package = $packages->get(1, contain: ['Categories', 'Tags']);
        $this->assertCount(2, $package->categories);
        $this->assertSame('Updated plugin description', $package->description);
        $this->assertSame('markstory', $package->namespace);
        $this->assertSame(1234, $package->monthly_downloads);
        $this->assertSame('5.1.0', $package->latest_stable_version);
        $this->assertEquals(new Date('2026-04-05'), $package->latest_stable_release_date);
        $this->assertContains('cakephp-5-4', array_column($package->tags, 'slug'));
        $this->assertContains('php-8-5', array_column($package->tags, 'slug'));
    }

    /**
     * A broken package index cannot wipe the catalogue.
     */
    public function testEmptyIndexRetainsExistingPackages(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $client = $this->createStub(PackagistClient::class);
        $client->method('all')->willReturn([]);
        try {
            new PackagistImporter($packages, $client)->sync();
            $this->fail('The empty index should have been rejected.');
        } catch (UnexpectedValueException) {
            $this->assertSame(26, $packages->find()->count());
        }
    }

    /**
     * @return void
     */
    public function testHasExplicitCakePhpDependency(): void
    {
        $command = new PackagistImporter($this->fetchTable(PackagesTable::class), $this->createStub(PackagistClient::class));
        $method = new ReflectionMethod($command, 'hasExplicitCakePhpDependency');

        $this->assertTrue($method->invoke($command, ['PHP: 8.2', 'CakePHP: 5.0']));
        $this->assertFalse($method->invoke($command, ['PHP: 8.2']));
    }

    /**
     * @return void
     */
    public function testExtractReleaseDate(): void
    {
        $command = new PackagistImporter($this->fetchTable(PackagesTable::class), $this->createStub(PackagistClient::class));
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
            $container = new Container();
            new Application(dirname(__DIR__, 3) . '/config')->services($container);
            /** @var \Cake\Http\Client $client */
            $client = $container->get('packagist.http');
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
}
