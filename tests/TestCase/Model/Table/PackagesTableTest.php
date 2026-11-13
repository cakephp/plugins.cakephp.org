<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PackagesTable;
use Cake\Cache\Cache;
use Cake\TestSuite\TestCase;
use Override;

/**
 * Covers the directory's search and its inclusion of configured featured packages.
 */
class PackagesTableTest extends TestCase
{
    /**
     * @var list<string>
     */
    #[Override]
    protected array $fixtures = ['app.Packages', 'app.Categories', 'app.CategoriesPackages', 'app.PublisherVersions', 'app.PublisherTagged'];

    /**
     * Version options must reflect this test's compatibility fixtures.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Cache::delete('package_filter_tags_v1');
    }

    /**
     * Grid counts represent unique packages in each category across the directory.
     */
    public function testCategoryChoicesIncludePackageCounts(): void
    {
        $this->assertSame([
            'asset-management' => ['name' => 'Asset Management', 'count' => 1],
            'authentication-authorization' => ['name' => 'Authentication & Authorization', 'count' => 1],
            'code-analysis' => ['name' => 'Code Analysis', 'count' => 1],
        ], $this->fetchTable(PackagesTable::class)->categoryOptions());
    }

    /**
     * Membership is optional and multi-category plugins appear only once.
     */
    public function testCategoryAndMembershipFilters(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $this->assertSame(2, $packages->find('catalogue', filters: ['awesome' => true])->count());
        $results = $packages->find('catalogue', filters: ['category' => 'asset-management'])->toArray();
        $this->assertCount(1, $results);
        $this->assertSame('markstory/asset_compress', $results[0]->package);
        $this->assertCount(2, $results[0]->categories);
        $this->assertSame(0, $packages->find('catalogue', filters: ['category' => 'asset-management', 'search' => 'users'])->count());
        $this->assertSame(1, $packages->find('catalogue', filters: ['awesome' => true, 'search' => 'users'])->count());
        $this->assertSame(26, $packages->find('catalogue', filters: [])->count());
    }

    /**
     * Names and descriptions are both searchable in the full catalogue.
     */
    public function testCatalogueMatchesNameAndDescription(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        foreach (['asset_compress', 'slider'] as $term) {
            $results = $packages->find('catalogue', filters: ['search' => $term])->toArray();
            $this->assertCount(1, $results);
            $this->assertSame('markstory/asset_compress', $results[0]->package);
        }
    }

    /**
     * No-match searches are empty, while an unfiltered catalogue includes featured entries.
     */
    public function testCatalogueContainsEveryPackage(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $this->assertSame(26, $packages->find('catalogue', filters: [])->count());
        $this->assertSame(0, $packages->find('catalogue', filters: ['search' => 'no-matching-plugin'])->count());
    }

    /**
     * The editorial picker searches canonical names in a stable order.
     */
    public function testFeaturedCandidates(): void
    {
        $results = $this->fetchTable(PackagesTable::class)->find('featuredCandidates', search: ' package-0 ')->all();
        $this->assertSame([
            'vendor/package-01', 'vendor/package-02', 'vendor/package-03',
            'vendor/package-04', 'vendor/package-05', 'vendor/package-06',
            'vendor/package-07', 'vendor/package-08', 'vendor/package-09',
        ], $results->extract('package')->toList());
    }

    /**
     * Namespace matching is literal and ends at the Composer vendor separator.
     */
    public function testNamespaceMatching(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $names = [
            2 => 'vendor_extra/one',
            3 => 'vendorXextra/two',
            4 => 'vendor_extra_more/three',
            5 => 'vendor.extra/four',
        ];
        foreach ($names as $id => $name) {
            $package = $packages->get($id);
            $package->package = $name;
            $packages->saveOrFail($package);
        }
        $this->assertSame([2], $packages->find('namespace', namespace: 'vendor_extra')->all()->extract('id')->toList());
        $this->assertSame([5], $packages->find('namespace', namespace: 'vendor.extra')->all()->extract('id')->toList());
        $this->assertSame(0, $packages->find('namespace', namespace: 'vendor%')->count());
        $this->assertSame(0, $packages->find('namespace', namespace: "vendor' OR 1=1")->count());
        $this->assertSame(19, $packages->find('namespace', namespace: 'vendor')->count());
    }

    /**
     * Imports derive the namespace, and renames cannot retain a stale or forged vendor.
     */
    public function testNamespaceIsDerivedOnCreateAndRename(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $package = $packages->newEntity([
            'package' => 'Example_Vendor/plugin',
            'namespace' => 'forged',
            'description' => 'Example package.',
            'repo_url' => 'https://github.com/example/plugin',
            'downloads' => 100,
            'stars' => 10,
            'latest_stable_version' => '1.0.0',
            'latest_stable_release_date' => null,
        ]);
        $packages->saveOrFail($package);
        $this->assertSame('example_vendor', $packages->get($package->id)->namespace);
        $package = $packages->patchEntity($package, [
            'package' => 'different.vendor/plugin',
            'namespace' => 'forged-again',
        ]);
        $packages->saveOrFail($package);
        $this->assertSame('different.vendor', $packages->get($package->id)->namespace);
        $this->assertSame(0, $packages->find('namespace', namespace: 'example_vendor')->count());
        $this->assertSame(1, $packages->find('namespace', namespace: 'different.vendor')->count());
    }

    /**
     * Totals and category counts cover only the requested namespace.
     */
    public function testNamespaceStatisticsAndCategories(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $this->assertSame([
            'plugins' => 23,
            'downloads' => 22701,
            'stars' => 2001,
        ], $packages->namespaceStats('vendor'));
        $this->assertSame([
            'plugins' => 0,
            'downloads' => 0,
            'stars' => 0,
        ], $packages->namespaceStats('missing'));
        $this->assertSame([
            'asset-management' => [
                'name' => 'Asset Management',
                'count' => 1,
            ],
            'code-analysis' => [
                'name' => 'Code Analysis',
                'count' => 1,
            ],
        ], $packages->categoryOptions('markstory'));
        $this->assertSame([], $packages->categoryOptions('vendor'));
        $this->assertSame(1, $packages->find('catalogue', filters: ['awesome' => true], namespace: 'markstory')->count());
        $this->assertSame(0, $packages->find('catalogue', filters: ['search' => 'users'], namespace: 'markstory')->count());
        $this->assertSame(0, $packages->find('catalogue', filters: ['category' => 'authentication-authorization'], namespace: 'markstory')->count());
    }

    /**
     * Repository URLs identify a profile independently of the Composer namespace.
     */
    public function testNamespaceGithubProfile(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $this->assertSame('https://github.com/vendor', $packages->namespaceGithubUrl('vendor'));
        $packages->updateAll(['repo_url' => 'https://www.github.com/Actual-Owner/plugin.git'], ['id' => 1]);
        $this->assertSame('https://github.com/actual-owner', $packages->namespaceGithubUrl('markstory'));
        $packages->updateAll(['repo_url' => 'git@github.com:Actual-Owner/plugin.git'], ['id' => 1]);
        $this->assertSame('https://github.com/actual-owner', $packages->namespaceGithubUrl('markstory'));
        $packages->updateAll(['repo_url' => 'https://github.com/another-owner/plugin'], ['id' => 2]);
        $this->assertNull($packages->namespaceGithubUrl('vendor'));
        $unsupportedUrls = [
            'https://gitlab.com/markstory/plugin',
            'https://github.com.example.com/markstory/plugin',
            'https://github.com/markstory',
            'https://github.com/invalid_owner/plugin',
        ];
        foreach ($unsupportedUrls as $url) {
            $packages->updateAll(['repo_url' => $url], ['id' => 1]);
            $this->assertNull($packages->namespaceGithubUrl('markstory'));
        }
        $this->assertNull($packages->namespaceGithubUrl('missing'));
    }

    /**
     * Rankings use namespace totals, with stable alphabetical ties.
     */
    public function testPublisherRankings(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $downloads = $packages->unhydratedFind('publishers')->all()->toList();
        $this->assertSame(['vendor', 'dereuromark', 'cakedc', 'markstory'], array_column($downloads, 'namespace'));
        $this->assertEquals([
            'namespace' => 'vendor',
            'plugins' => 23,
            'downloads' => 22701,
            'monthly_downloads' => 2001,
            'stars' => 2001,
            'latest_release' => '2025-02-24',
        ], $downloads[0]);
        $this->assertSame(['vendor', 'cakedc', 'markstory', 'dereuromark'], $packages->unhydratedFind('publishers', sort: 'stars')->all()->extract('namespace')->toList());
        $this->assertSame(['vendor', 'cakedc', 'dereuromark', 'markstory'], $packages->unhydratedFind('publishers', sort: 'plugins')->all()->extract('namespace')->toList());
        $this->assertEquals($downloads, $packages->unhydratedFind('publishers', sort: 'unknown')->all()->toList());
        $packages->updateAll(['downloads' => 5000], ['id' => 25]);
        $this->assertSame(['vendor', 'dereuromark', 'cakedc', 'markstory'], $packages->unhydratedFind('publishers', sort: 'downloads')->all()->extract('namespace')->toList());
    }

    /**
     * Search uses literal substrings rather than SQL wildcard semantics.
     */
    public function testPublisherSearch(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $this->assertSame(['dereuromark'], $packages->unhydratedFind('publishers', search: ' REURO ')->all()->extract('namespace')->toList());
        $this->assertSame(0, $packages->unhydratedFind('publishers', search: '%')->count());
        $package = $packages->get(2);
        $package->package = 'vendor_extra/package';
        $packages->saveOrFail($package);
        $this->assertSame(['vendor_extra'], $packages->unhydratedFind('publishers', search: '_')->all()->extract('namespace')->toList());
    }

    /**
     * Previews are bounded, metric-aware, and consider every repository owner.
     */
    public function testPublisherDetails(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $details = $packages->publisherDetails(['vendor', 'markstory']);
        $this->assertSame(['vendor/package-01', 'vendor/package-02'], array_column($details['vendor']['plugins'], 'package'));
        $this->assertSame('https://github.com/vendor', $details['vendor']['githubUrl']);
        $this->assertCount(1, $details['markstory']['plugins']);
        $packages->updateAll(['stars' => 999, 'repo_url' => 'https://github.com/another-owner/plugin'], ['id' => 24]);
        $this->assertNull($packages->publisherDetails(['vendor'])['vendor']['githubUrl']);
        $this->assertSame(['vendor/package-23', 'vendor/package-01'], array_column($packages->publisherDetails(['vendor'], 'stars')['vendor']['plugins'], 'package'));
        $this->assertSame(['vendor/package-01', 'vendor/package-02'], array_column($packages->publisherDetails(['vendor'], 'plugins')['vendor']['plugins'], 'package'));
        $this->assertSame([], $packages->publisherDetails([]));
    }

    /**
     * Legacy-only packages cannot inflate current-major totals or previews.
     */
    public function testPublisherCompatibilityScope(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $this->assertSame(['5', '4'], $packages->publisherMajors());
        $packages->updateAll([
            'monthly_downloads' => 999999,
            'downloads' => 99999999,
            'stars' => 99999,
        ], ['id' => 25]);
        $this->assertSame('cakedc', $packages->unhydratedFind('publishers')->first()['namespace']);
        $current = $packages->unhydratedFind('publishers', major: '5')->all()->toList();
        $this->assertSame(['vendor', 'dereuromark', 'markstory'], array_column($current, 'namespace'));
        $this->assertEquals(1, $current[2]['plugins']);
        $this->assertEquals(150, $current[2]['monthly_downloads']);
        $this->assertSame('2025-01-15', $current[2]['latest_release']);

        $packages->Tagged->updateAll(['tag_id' => 3], ['fk_id' => 24, 'fk_model' => 'Packages']);
        $packages->updateAll([
            'monthly_downloads' => 999999,
            'repo_url' => 'https://github.com/another-owner/plugin',
        ], ['id' => 24]);
        $vendor = $packages->unhydratedFind('publishers', major: '5', search: 'vendor')->first();
        $this->assertEquals(22, $vendor['plugins']);
        $this->assertEquals(1925, $vendor['monthly_downloads']);
        $details = $packages->publisherDetails(['vendor'], major: '5');
        $this->assertSame(['package-01', 'package-02'], array_column($details['vendor']['plugins'], 'name'));
        $this->assertNull($details['vendor']['githubUrl']);
        $this->assertSame('cakedc', $packages->unhydratedFind('publishers', major: '4')->first()['namespace']);
    }

    /**
     * Recent usage can reverse lifetime rankings; missing data is never treated as zero.
     */
    public function testMonthlyRankingsAndUnknownCounts(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $packages->updateAll(['monthly_downloads' => 3000], ['id' => 1]);
        $this->assertSame('markstory', $packages->unhydratedFind('publishers', major: '5')->first()['namespace']);
        $this->assertSame('vendor', $packages->unhydratedFind('publishers', major: '5', sort: 'downloads')->first()['namespace']);
        $packages->updateAll(['monthly_downloads' => null], ['id' => 2]);
        $vendor = $packages->unhydratedFind('publishers', search: 'vendor')->first();
        $this->assertNull($vendor['monthly_downloads']);
        $packages->updateAll(['monthly_downloads' => 0], ['id' => 1]);
        $this->assertSame(['dereuromark', 'markstory', 'vendor'], $packages->unhydratedFind('publishers', major: '5')->all()->extract('namespace')->toList());
        $this->assertEquals(0, $packages->unhydratedFind('publishers', search: 'markstory')->first()['monthly_downloads']);
    }
}
