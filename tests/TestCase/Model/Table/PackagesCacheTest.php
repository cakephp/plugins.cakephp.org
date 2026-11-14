<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Command\CleanCommand;
use App\Model\Table\PackagesTable;
use App\Service\AwesomeListImporter;
use App\Service\AwesomeListParser;
use App\Service\PackagistImporter;
use Cake\Cache\Cache;
use Cake\Cache\Engine\FileEngine;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Http\Client;
use Cake\TestSuite\TestCase;
use Override;
use Packagist\Api\Client as PackagistClient;
use Packagist\Api\Result\Package;
use Packagist\Api\Result\Package\Downloads;
use Packagist\Api\Result\Package\Version;

/**
 * Exercise serialized query results independently of any development Redis data.
 */
class PackagesCacheTest extends TestCase
{
    /**
     * @var list<string>
     */
    #[Override]
    protected array $fixtures = ['app.Packages', 'app.Categories', 'app.CategoriesPackages', 'app.PublisherVersions', 'app.PublisherTagged'];

    /**
     * @var array<string, mixed>
     */
    private array $cacheConfig;

    /**
     * Use persistent serialization, just as Redis does, with isolated fixture data.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheConfig = Cache::getConfigOrFail('catalogue');
        Cache::drop('catalogue');
        Cache::setConfig('catalogue', [
            'className' => FileEngine::class,
            'path' => TMP . 'tests' . DS . 'catalogue' . DS,
            'prefix' => 'test_catalogue_',
            'duration' => 86400,
        ]);
        Cache::clear('catalogue');
    }

    /**
     * Never let cached fixtures escape this test.
     */
    protected function tearDown(): void
    {
        Cache::clear('catalogue');
        Cache::drop('catalogue');
        Cache::setConfig('catalogue', $this->cacheConfig);
        parent::tearDown();
    }

    /**
     * Repeated reads retain rows, associations and totals until invalidation.
     */
    public function testCatalogueCachesRowsAssociationsAndTotals(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $query = $packages->find('catalogue', filters: [])->orderByAsc('Packages.id')->limit(2);
        $before = $query->all()->toArray();
        $this->assertSame(26, $query->count());
        $this->assertCount(2, $before[0]->categories);
        $this->assertNotEmpty($before[0]->tags);
        $packages->updateAll(['description' => 'Changed after warming cache'], ['id' => 1]);
        $packages->Categories->junction()->deleteAll(['package_id' => 1]);
        $packages->getConnection()->delete('packages', ['id' => 24]);

        $cachedQuery = $packages->find('catalogue', filters: [])->orderByAsc('Packages.id')->limit(2);
        $cached = $cachedQuery->all()->toArray();
        $this->assertSame($before[0]->description, $cached[0]->description);
        $this->assertCount(2, $cached[0]->categories);
        $this->assertEquals($before[0]->tags, $cached[0]->tags);
        $this->assertSame(26, $cachedQuery->count());

        Cache::clear('catalogue');
        $freshQuery = $packages->find('catalogue', filters: [])->orderByAsc('Packages.id')->limit(2);
        $fresh = $freshQuery->all()->toArray();
        $this->assertSame('Changed after warming cache', $fresh[0]->description);
        $this->assertSame([], $fresh[0]->categories);
        $this->assertSame(25, $freshQuery->count());
    }

    /**
     * Filters, bindings, namespace, direction, page and limit cannot collide.
     */
    public function testCatalogueCacheSeparatesSelections(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $cases = [
            [[], null, 'ASC', 1, 1, [1], 26],
            [[], null, 'ASC', 2, 1, [2], 26],
            [[], null, 'DESC', 1, 2, [26, 25], 26],
            [[], 'vendor', 'ASC', 1, 1, [2], 23],
            [['search' => 'package-01'], null, 'ASC', 1, 1, [2], 1],
            [['search' => 'package-02'], null, 'ASC', 1, 1, [3], 1],
            [['category' => 'asset-management'], null, 'ASC', 1, 1, [1], 1],
            [['awesome' => true], null, 'ASC', 1, 2, [1, 25], 2],
            [['cakephp_slugs' => ['cakephp-4-4']], null, 'ASC', 1, 1, [25], 1],
        ];
        // Run twice: the second pass reads every variant from persistent cache.
        for ($pass = 0; $pass < 2; $pass++) {
            foreach ($cases as [$filters, $namespace, $direction, $page, $limit, $ids, $total]) {
                $query = $packages->find('catalogue', filters: $filters, namespace: $namespace)
                    ->orderBy(['Packages.id' => $direction])->limit($limit)->page($page);
                $this->assertSame($ids, $query->all()->extract('id')->toList());
                $this->assertSame($total, $query->count());
            }
        }
    }

    /**
     * Equivalent version selections reuse cached rows and counts despite URL order.
     */
    public function testVersionSelectionOrderAndDuplicatesShareCache(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $query = $packages->find('catalogue', filters: [
            'cakephp_slugs' => ['cakephp-5-4', 'cakephp-4-4'],
        ])->orderByAsc('Packages.id')->limit(2);
        $before = $query->all()->toArray();
        $this->assertSame(26, $query->count());
        $packages->updateAll(['description' => 'Changed after cache warmup'], ['id' => 1]);
        $packages->getConnection()->delete('packages', ['id' => 26]);

        $filters = ['cakephp_slugs' => ['cakephp-4-4', 'cakephp-5-4', 'cakephp-4-4']];
        $cached = $packages->find('catalogue', filters: $filters)->orderByAsc('Packages.id')->limit(2);
        $this->assertSame($before[0]->description, $cached->all()->first()->description);
        $this->assertSame(26, $cached->count());

        Cache::clear('catalogue');
        $fresh = $packages->find('catalogue', filters: $filters)->orderByAsc('Packages.id')->limit(2);
        $this->assertSame('Changed after cache warmup', $fresh->all()->first()->description);
        $this->assertSame(25, $fresh->count());
    }

    /**
     * Publisher aggregates retain grouping, compatibility, search and hydration.
     */
    public function testPublisherCacheSeparatesRankingsAndDetails(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        foreach ([['4', '', 1], ['5', '', 3], [null, 'vendor', 1], [null, 'cakedc', 1]] as [$major, $search, $total]) {
            foreach (['plugins', 'downloads', 'monthly_downloads', 'stars'] as $sort) {
                $query = $packages->unhydratedFind('publishers', search: $search, sort: $sort, major: $major)->limit(1);
                $before = $query->all()->toArray();
                $this->assertSame($total, $query->count());
                $cached = $packages->unhydratedFind('publishers', search: $search, sort: $sort, major: $major)->limit(1);
                $this->assertSame($before, $cached->all()->toArray());
                $this->assertSame($total, $cached->count());
            }
        }
        $this->assertIsArray($packages->unhydratedFind('publishers')->first());
        $this->assertIsObject($packages->find('publishers')->first());
        $before = $packages->publisherDetails(['vendor']);
        $packages->updateAll(['monthly_downloads' => 100000], ['id' => 24]);
        $this->assertSame($before, $packages->publisherDetails(['vendor']));
        Cache::clear('catalogue');
        $this->assertSame('vendor/package-23', $packages->publisherDetails(['vendor'])['vendor']['plugins'][0]['package']);
    }

    /**
     * Normal ORM reads stay fresh for editors while public detail reads are cached.
     */
    public function testDetailsAndNamespaceDataUsePublicCacheOnly(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $before = $packages->get(1, finder: 'publicDetails');
        $stats = $packages->namespaceStats('markstory');
        $owner = $packages->namespaceGithubUrl('markstory');
        $categories = $packages->categoryOptions('markstory');
        $versions = $packages->filterOptions();
        $packages->updateAll([
            'description' => 'Fresh editor data',
            'downloads' => 123456,
            'repo_url' => 'https://github.com/newowner/asset_compress',
        ], ['id' => 1]);
        $packages->Categories->junction()->deleteAll(['package_id' => 1]);
        $packages->Tags->getTarget()->updateAll(['label' => 'CakePHP: 5.5'], ['slug' => 'cakephp-5-4']);

        $this->assertSame('Fresh editor data', $packages->get(1)->description);
        $this->assertSame($before->description, $packages->get(1, finder: 'publicDetails')->description);
        $this->assertSame('cakedc/users', $packages->get(25, finder: 'publicDetails')->package);
        $this->assertSame($stats, $packages->namespaceStats('markstory'));
        $this->assertSame($owner, $packages->namespaceGithubUrl('markstory'));
        $this->assertSame($categories, $packages->categoryOptions('markstory'));
        $this->assertSame($versions, $packages->filterOptions());

        Cache::clear('catalogue');
        $this->assertSame('Fresh editor data', $packages->get(1, finder: 'publicDetails')->description);
        $this->assertSame(123456, $packages->namespaceStats('markstory')['downloads']);
        $this->assertSame('https://github.com/newowner', $packages->namespaceGithubUrl('markstory'));
        $this->assertSame([], $packages->categoryOptions('markstory'));
        $this->assertSame('CakePHP: 5.5', $packages->filterOptions()['cakephp']['cakephp-5-4']);
    }

    /**
     * Expiry refreshes serialized public data without explicit invalidation.
     */
    public function testCacheExpiryRefreshesDetails(): void
    {
        $cache = Cache::pool('catalogue');
        $this->assertInstanceOf(FileEngine::class, $cache);
        $this->assertSame(86400, $cache->getConfig('duration'));
        $cache->setConfig('duration', 1);
        $packages = $this->fetchTable(PackagesTable::class);
        $packages->get(1, finder: 'publicDetails');
        $packages->updateAll(['description' => 'Updated during TTL'], ['id' => 1]);
        usleep(2100000);
        $this->assertSame('Updated during TTL', $packages->get(1, finder: 'publicDetails')->description);
    }

    /**
     * Synchronizing curation refreshes cached associations and category choices.
     */
    public function testCategoryImportInvalidatesPublicData(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $this->assertCount(2, $packages->get(1, finder: 'publicDetails')->categories);
        $this->assertCount(3, $packages->categoryOptions());
        $importer = new AwesomeListImporter($packages, $this->createStub(Client::class), new AwesomeListParser());
        $importer->import("## Plugins\n### Assets\n- [Assets](https://github.com/markstory/asset_compress)\n");
        $this->assertSame(['assets'], array_keys($packages->categoryOptions()));
        $this->assertSame('assets', $packages->get(1, finder: 'publicDetails')->categories[0]->slug);
        $this->assertCount(1, $packages->get(1, finder: 'publicDetails')->categories);
    }

    /**
     * Package synchronization invalidates cached records, totals and compatibility.
     */
    public function testPackageImportInvalidatesPublicData(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $packages->get(1, finder: 'publicDetails');
        $this->assertSame(26, $packages->find('catalogue', filters: [])->count());
        $packages->filterOptions();
        $version = new Version();
        $version->fromArray([
            'version' => '5.1.0',
            'time' => '2026-04-05T11:22:33+00:00',
            'require' => [
                'php' => '^8.2',
                'cakephp/cakephp' => '^5.0',
            ],
        ]);
        $downloads = new Downloads();
        $downloads->fromArray([
            'total' => 12345,
            'monthly' => 1234,
        ]);
        $metadata = new Package();
        $metadata->fromArray([
            'description' => 'Imported metadata',
            'repository' => 'https://github.com/markstory/asset_compress',
            'downloads' => $downloads,
            'github_stars' => 500,
            'versions' => [$version],
        ]);
        $client = $this->createStub(PackagistClient::class);
        $client->method('all')->willReturn(['markstory/asset_compress']);
        $client->method('get')->willReturn($metadata);
        new PackagistImporter($packages, $client)->sync();
        $this->assertSame(1, $packages->find('catalogue', filters: [])->count());
        $this->assertSame('Imported metadata', $packages->get(1, finder: 'publicDetails')->description);
        $this->assertArrayHasKey('cakephp-5-0', $packages->filterOptions()['cakephp']);
    }

    /**
     * Confirmed catalogue cleanup must also remove cached rows and totals.
     */
    public function testCleanupInvalidatesPublicData(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $this->assertSame(26, $packages->find('catalogue', filters: [])->count());
        $this->assertSame(1, $packages->namespaceStats('markstory')['plugins']);
        $io = $this->createStub(ConsoleIo::class);
        $io->method('ask')->willReturn('yes');
        new CleanCommand()->execute(new Arguments([], [], []), $io);
        $this->assertSame(0, $packages->find('catalogue', filters: [])->count());
        $this->assertSame(0, $packages->namespaceStats('markstory')['plugins']);
    }
}
