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
    protected array $fixtures = ['app.Packages', 'plugin.Tags.Tags', 'plugin.Tags.Tagged'];

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
}
