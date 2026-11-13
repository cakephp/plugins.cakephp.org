<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PackagesTable;
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
    protected array $fixtures = ['app.Packages', 'app.Categories', 'app.CategoriesPackages', 'plugin.Tags.Tags', 'plugin.Tags.Tagged'];

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
}
