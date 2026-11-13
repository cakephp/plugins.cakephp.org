<?php
declare(strict_types=1);

namespace App\Test\TestCase\Island;

use App\Model\Table\FeaturedPluginsTable;
use App\Model\Table\PackagesTable;
use Cake\TestSuite\TestCase;
use Interact\TestSuite\IslandTestTrait;
use Override;

class FeaturedPluginsIslandTest extends TestCase
{
    use IslandTestTrait;

    #[Override]
    protected array $fixtures = ['app.FeaturedPlugins', 'app.Packages', 'plugin.Tags.Tags', 'plugin.Tags.Tagged'];

    /**
     * Keep mount order while honoring current enabled state on every update.
     */
    public function testSelectionOrderSurvivesRefresh(): void
    {
        $island = $this->testIsland('FeaturedPlugins');
        $order = $island->result()->state['order'];
        $this->fetchTable(FeaturedPluginsTable::class)->updateAll(['enabled' => false], ['id' => 1]);
        $island->sync()->assertSet('order', $order)->assertDontSee('markstory/asset_compress')->assertSee('cakedc/users');
    }

    /**
     * Empty and unavailable selections hide the featured region.
     */
    public function testMissingSelectionHasNoFeaturedEntries(): void
    {
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $this->fetchTable(PackagesTable::class)->deleteAll([]);
        $this->testIsland('FeaturedPlugins')->assertDontSee('data-featured-plugins');
        $table->deleteAll([]);
        $this->testIsland('FeaturedPlugins')->assertDontSee('data-featured-plugins');
    }

    /**
     * The random pool is bounded to three available, distinct packages.
     */
    public function testSelectionIsBoundedToThreePackages(): void
    {
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $table->saveOrFail($table->newEntity(['package' => 'vendor/package-01', 'enabled' => true]));
        $island = $this->testIsland('FeaturedPlugins');
        $this->assertSame(3, substr_count($island->result()->html, 'data-plugin-tone='));
    }

    /**
     * Editorial values are escaped and never change imported package metadata.
     */
    public function testEditorialOverrides(): void
    {
        $this->fetchTable(FeaturedPluginsTable::class)->updateAll([
            'title' => '<b>Curated title</b>', 'description' => '<script>example</script>', 'logo' => '/img/custom.svg',
        ], ['id' => 1]);
        $this->testIsland('FeaturedPlugins')->assertSee('<b>Curated title</b>')->assertSee('<script>example</script>')->assertSee('/img/custom.svg');
        $this->assertSame('Featured package used to verify the homepage slider.', $this->fetchTable(PackagesTable::class)->get(1)->description);
    }
}
