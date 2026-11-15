<?php
declare(strict_types=1);

namespace App\Test\TestCase\Island;

use App\Model\Table\FeaturedPluginsTable;
use App\Model\Table\PackagesTable;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Interact\TestSuite\IslandTestTrait;
use Override;

class CommunityPicksTest extends TestCase
{
    use IslandTestTrait;

    #[Override]
    protected array $fixtures = ['app.FeaturedPlugins', 'app.Packages', 'app.Categories', 'app.CategoriesPackages', 'plugin.Tags.Tags', 'plugin.Tags.Tagged'];

    /**
     * Picks are normal results, including while searching and after a reset.
     */
    public function testPicksStayInsideTheCatalogue(): void
    {
        $island = $this->testIsland('Catalogue')->set('paging.sort', 'downloads')->assertDontSee('data-featured-plugins');
        $this->assertSame(3, substr_count($island->result()->html, ' data-community-pick'));
        $this->assertSame(21, substr_count($island->result()->html, ' data-package-row'));
        $island->set('search', 'asset')->assertSee('Community pick')->assertSee('markstory/asset_compress');
        $this->assertSame(1, substr_count($island->result()->html, ' data-package-row'));
        $island->call('clearFilters')->assertDontSee('data-featured-plugins');
        $this->assertSame(3, substr_count($island->result()->html, ' data-community-pick'));
    }

    /**
     * Reloadable pick membership combines with search, category and namespace.
     */
    public function testPickFilterComposesAndResetsPaging(): void
    {
        $request = new ServerRequest(['url' => 'http://localhost/?featured=1'])->withQueryParams(['featured' => '1']);
        $island = $this->testIsland('Catalogue', request: $request)->assertSet('featuredOnly', true);
        $this->assertSame(3, substr_count($island->result()->html, ' data-package-row'));
        $island->call('selectCategory', 'asset-management')->assertSee('markstory/asset_compress')->assertDontSee('cakedc/users');
        $island->set('search', 'users')->assertSee('No plugins found');
        $island->call('clearFilters')->assertSet('featuredOnly', false)->set('paging.page', 2)
            ->call('toggleFeatured')->assertSet('paging.page', 1)->assertSet('featuredOnly', true);
        $this->testIsland('Catalogue', ['namespace' => 'vendor'])->call('toggleFeatured')->assertSee('No plugins found');
    }

    /**
     * Disabled and orphaned editorial entries never become visible picks.
     */
    public function testDisabledAndUnavailablePicksAreExcluded(): void
    {
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $entry = $table->get(1);
        $entry->enabled = false;
        $table->saveOrFail($entry);
        $this->fetchTable(PackagesTable::class)->deleteAll(['package' => 'dereuromark/cakephp-tools']);
        $island = $this->testIsland('Catalogue')->call('toggleFeatured')->assertDontSee('markstory/asset_compress')->assertDontSee('dereuromark/cakephp-tools');
        $this->assertSame(1, substr_count($island->result()->html, ' data-package-row'));
    }

    /**
     * Editorial overrides are escaped and leave imported package data intact.
     */
    public function testEditorialOverrides(): void
    {
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $entry = $table->get(1);
        $entry->patch(['title' => '<b>Curated title</b>', 'description' => '<script>example</script>', 'logo' => '/img/custom.svg']);
        $table->saveOrFail($entry);
        $island = $this->testIsland('Catalogue')->set('search', 'asset')
            ->assertSee('<b>Curated title</b>')->assertSee('<script>example</script>')->assertSee('/img/custom.svg');
        $this->assertStringNotContainsString('<script>example</script>', $island->result()->html);
        $this->assertSame('Featured package used to verify the homepage slider.', $this->fetchTable(PackagesTable::class)->get(1)->description);
    }
}
