<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Enum\FeaturedPluginStatus;
use App\Model\Table\FeaturedPluginsTable;
use App\Model\Table\PackagesTable;
use Cake\TestSuite\TestCase;
use Override;

class FeaturedPluginsTableTest extends TestCase
{
    #[Override]
    protected array $fixtures = ['app.FeaturedPlugins', 'app.Packages'];

    /**
     * Every ORM caller must select a real catalogue package for a new feature.
     */
    public function testNewEntriesRequireAnExistingUniquePackage(): void
    {
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        foreach (['missing/package', 'markstory/asset_compress'] as $package) {
            $entry = $table->newEntity([
                'package' => $package,
                'enabled' => true,
            ]);
            $this->assertFalse($table->save($entry));
            $this->assertNotEmpty($entry->getError('package'));
        }
        $entry = $table->newEntity([
            'package' => 'vendor/package-01',
            'enabled' => true,
        ]);
        $this->assertNotFalse($table->save($entry));
    }

    /**
     * Catalogue deletion does not prevent later editorial changes.
     */
    public function testUnavailableEntriesRemainEditable(): void
    {
        $this->fetchTable(PackagesTable::class)->deleteAll(['package' => 'markstory/asset_compress']);
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $entry = $table->patchEntity($table->get(1), [
            'enabled' => false,
            'title' => 'Still curated',
        ]);
        $table->saveOrFail($entry);
        $this->assertFalse($table->get(1)->enabled);
        $this->assertSame('Still curated', $table->get(1)->title);
    }

    /**
     * Model normalization preserves the old editor's trimming and null fallbacks.
     */
    public function testOverridesAreNormalizedBeforeValidationAndPersistence(): void
    {
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $entry = $table->patchEntity($table->get(1), [
            'logo' => ' /img/pick.svg ',
            'title' => ' Our pick ',
            'description' => '  ',
        ]);
        $table->saveOrFail($entry);
        $entry = $table->get(1);
        $this->assertSame('/img/pick.svg', $entry->logo);
        $this->assertSame('Our pick', $entry->title);
        $this->assertNull($entry->description);
        $table->saveOrFail($table->patchEntity($entry, [
            'logo' => '  ',
            'title' => ' ',
            'description' => '0',
        ]));
        $entry = $table->get(1);
        $this->assertNull($entry->logo);
        $this->assertNull($entry->title);
        $this->assertSame('0', $entry->description);
    }

    /**
     * An unfinished or unsafe logo uses the fallback instead of an image URL.
     */
    public function testInvalidLogosAreExcludedFromPreviewEntities(): void
    {
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $this->assertSame('/img/pick.svg', $table->newEntity(['logo' => ' /img/pick.svg '])->logo);
        $this->assertSame('https://example.com/logo.svg', $table->newEntity(['logo' => 'https://example.com/logo.svg'])->logo);
        foreach (['', ' ', 'https://', 'javascript:alert(1)', '/img/../private.svg'] as $logo) {
            $this->assertNull($table->newEntity(['logo' => $logo])->logo);
        }
    }

    /**
     * Listing filters share one query with the catalogue association.
     */
    public function testAdminListingFilters(): void
    {
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $table->updateAll(['enabled' => false], ['id' => 2]);
        $this->fetchTable(PackagesTable::class)->deleteAll(['package' => 'markstory/asset_compress']);
        $this->assertSame(2, $table->find('adminListing', status: FeaturedPluginStatus::Enabled)->count());
        $this->assertSame(1, $table->find('adminListing', status: FeaturedPluginStatus::Disabled)->count());
        $missing = $table->find('adminListing', status: FeaturedPluginStatus::Unavailable)->firstOrFail();
        $this->assertSame(1, $missing->id);
        $this->assertNull($missing->catalogue_package);
        $this->assertSame(1, $table->find('adminListing', search: ' cakedc ')->count());
        $this->assertSame(0, $table->find('adminListing', search: 'cakedc', status: FeaturedPluginStatus::Enabled)->count());
    }
}
