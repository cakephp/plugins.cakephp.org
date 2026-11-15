<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Application;
use App\Model\Entity\Package as PackageEntity;
use App\Model\Table\PackagesTable;
use App\Service\PackagistImporter;
use App\Utility\CakephpVersion;
use Cake\Cache\Cache;
use Cake\Container\Container;
use Cake\Core\Configure;
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
     * Numeric development branches and stability flags retain their source plugin version.
     */
    #[DataProvider('developmentConstraints')]
    public function testDevelopmentConstraints(string $constraint, bool $supported): void
    {
        $version = new Version();
        $version->fromArray(['version' => 'dev-next', 'time' => '2026-11-15T12:00:00+00:00', 'require' => ['cakephp/cakephp' => $constraint, 'php' => '^8.5']]);
        $client = $this->developmentClient([$version]);
        $result = new PackagistImporter($this->fetchTable(PackagesTable::class), $client)->sync();
        $this->assertSame($supported ? 1 : 0, $result['saved']);
        $package = $this->fetchTable(PackagesTable::class)->find()->where(['package' => 'future/development-plugin'])->first();
        if (!$supported) {
            $this->assertNull($package);

            return;
        }
        $this->assertNotNull($package);
        $this->assertNull($package->latest_stable_version);
        $this->assertSame('dev-next', $this->importedDevelopmentRelease($package)['version']);
        $this->assertSame('dev', $this->importedDevelopmentRelease($package)['stability']);
        $this->assertSame('^8.5', $this->importedDevelopmentRelease($package)['php_constraint']);
        $this->assertSame(1, $this->fetchTable(PackagesTable::class)->find('catalogue', filters: ['cakephp_slugs' => ['cakephp-6-x-dev']])->count());
        $this->assertSame(0, $this->fetchTable(PackagesTable::class)->find('catalogue', filters: [])->count());
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function developmentConstraints(): array
    {
        return [
            'stability flag' => ['6.x@dev', true],
            'numeric branch' => ['6.x-dev', true],
            'caret development' => ['^6.0@dev', true],
            'multiple majors' => ['^5.0 || ^6.0', true],
            'inline alias' => ['dev-main as 6.0.x-dev', true],
            'old framework' => ['^5.0', false],
            'wildcard is not explicit support' => ['*', false],
            'unbounded range is not explicit support' => ['>=4.0', false],
            'unknown named framework branch' => ['dev-main', false],
            'malformed constraint' => ['this is not a constraint', false],
        ];
    }

    /**
     * Released compatible plugin tags take precedence, while removal is reflected on the next sync.
     */
    public function testDevelopmentReleaseSelectionAndRemoval(): void
    {
        $stable = new Version();
        $stable->fromArray(['version' => '2.0.0', 'time' => '2026-10-01T00:00:00+00:00', 'require' => ['cakephp/cakephp' => '^6.0']]);
        $dev = new Version();
        $dev->fromArray(['version' => 'dev-next', 'time' => '2026-11-15T00:00:00+00:00', 'require' => ['cakephp/cakephp' => '6.x-dev']]);
        $packages = $this->fetchTable(PackagesTable::class);
        $importer = new PackagistImporter($packages, $this->developmentClient([$stable, $dev], 100));
        $importer->sync();
        $package = $packages->find()->where(['package' => 'future/development-plugin'])->firstOrFail();
        $this->assertSame('2.0.0', $package->latest_stable_version);
        $this->assertSame('2.0.0', $this->importedDevelopmentRelease($package)['version']);
        $stable->fromArray(['version' => '2.0.0', 'require' => ['cakephp/cakephp' => '^5.0']]);
        $importer->sync();
        $package = $packages->get($package->id);
        $this->assertSame('dev-next', $this->importedDevelopmentRelease($package)['version']);
        $dev->fromArray(['version' => 'dev-next', 'require' => ['cakephp/cakephp' => '^5.0']]);
        $importer->sync();
        $package = $packages->get($package->id, contain: ['Tags']);
        $this->assertSame([], $package->development_compatibility);
        $this->assertNotContains('cakephp-6-x-dev', array_column($package->tags, 'slug'));
    }

    /**
     * A single version configuration change promotes compatibility, filters and rankings on sync.
     */
    public function testPromotingDevelopmentMajorToStable(): void
    {
        $configuration = Configure::readOrFail('Packages.cakephpVersions');
        $version = new Version();
        $version->fromArray(['version' => '2.0.0', 'require' => ['cakephp/cakephp' => '^6.0']]);
        $packages = $this->fetchTable(PackagesTable::class);
        $importer = new PackagistImporter($packages, $this->developmentClient([$version], 100));
        try {
            $importer->sync();
            $package = $packages->find()->where(['package' => 'future/development-plugin'])->firstOrFail();
            $this->assertSame('2.0.0', $this->importedDevelopmentRelease($package)['version']);
            $this->assertArrayHasKey('cakephp-6-x-dev', $packages->filterOptions()['cakephp']);
            $this->assertNotContains('6', $packages->publisherMajors());

            Configure::write('Packages.cakephpVersions.6', [0]);
            $importer->sync();
            $package = $packages->get($package->id, contain: ['Tags']);
            $this->assertSame([], $package->development_compatibility);
            $this->assertNotContains('cakephp-6-x-dev', array_column($package->tags, 'slug'));
            $this->assertContains('cakephp-6-0', array_column($package->tags, 'slug'));
            // The Tags plugin retains unused tag rows; retired choices must still disappear.
            $this->assertTrue($packages->Tags->exists(['slug' => 'cakephp-6-x-dev']));
            $this->assertArrayNotHasKey('cakephp-6-x-dev', $packages->filterOptions()['cakephp']);
            $this->assertArrayHasKey('cakephp-6-0', $packages->filterOptions()['cakephp']);
            $this->assertFalse(CakephpVersion::isDevelopmentLabel('6.x@dev'));
            $this->assertSame(1, $packages->find('catalogue', filters: ['cakephp_slugs' => ['cakephp-6-0']])->count());
            $this->assertSame('6', $packages->publisherMajors()[0]);
            $this->assertSame(['major' => '6', 'plugins' => 1], $packages->ecosystemStats()['current_support']);
        } finally {
            Configure::write('Packages.cakephpVersions', $configuration);
            Cache::clear('catalogue');
        }
    }

    /**
     * Standalone CakePHP components must agree on support for the upcoming major.
     *
     * @param array<string, string> $requirements CakePHP component constraints.
     */
    #[DataProvider('componentRequirements')]
    public function testDevelopmentStandaloneComponents(array $requirements, bool $supported): void
    {
        $version = new Version();
        $version->fromArray(['version' => 'dev-main', 'require' => $requirements]);
        $packages = $this->fetchTable(PackagesTable::class);
        $importer = new PackagistImporter($packages, $this->developmentClient([$version]));
        $this->assertSame($supported ? 1 : 0, $importer->sync()['saved']);
    }

    /**
     * @return array<string, array{array<string, string>, bool}>
     */
    public static function componentRequirements(): array
    {
        return [
            'compatible components' => [['cakephp/orm' => '^6.0', 'cakephp/database' => '6.x-dev'], true],
            'conflicting components' => [['cakephp/orm' => '^6.0', 'cakephp/database' => '^5.0'], false],
            'framework and conflicting component' => [['cakephp/cakephp' => '^6.0', 'cakephp/orm' => '^5.0'], false],
            'core and container' => [['cakephp/core' => '^6.0', 'cakephp/container' => '^6.0'], true],
        ];
    }

    /**
     * @return array{version: string, stability: string, cakephp_constraint: string, php_constraint: string|null}
     */
    private function importedDevelopmentRelease(PackageEntity $package): array
    {
        $compatibility = $package->development_compatibility;
        $this->assertIsArray($compatibility);
        $this->assertArrayHasKey(6, $compatibility);

        return $compatibility[6];
    }

    /**
     * @param array<\Packagist\Api\Result\Package\Version> $versions Source releases.
     */
    private function developmentClient(array $versions, int $total = 0): PackagistClient
    {
        $downloads = new Downloads();
        $downloads->fromArray(['total' => $total, 'monthly' => 0]);
        $metadata = new Package();
        $metadata->fromArray([
            'description' => 'Development support', 'repository' => 'https://github.com/future/development-plugin',
            'downloads' => $downloads, 'github_stars' => 0, 'versions' => $versions,
        ]);
        $client = $this->createStub(PackagistClient::class);
        $client->method('all')->willReturn(['future/development-plugin']);
        $client->method('get')->willReturn($metadata);

        return $client;
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
     * Missing API download metadata must abort before stale-package cleanup.
     */
    public function testMissingDownloadsRetainExistingPackages(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $client = $this->createStub(PackagistClient::class);
        $client->method('all')->willReturn(['markstory/asset_compress']);
        $client->method('get')->willReturn(new Package());
        try {
            new PackagistImporter($packages, $client)->sync();
            $this->fail('Missing download metadata should have been rejected.');
        } catch (UnexpectedValueException $exception) {
            $this->assertStringContainsString('no download metadata', $exception->getMessage());
            $this->assertSame(26, $packages->find()->count());
            $this->assertSame(5000, $packages->get(1)->downloads);
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
