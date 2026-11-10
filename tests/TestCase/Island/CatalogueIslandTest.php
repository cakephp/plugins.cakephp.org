<?php
declare(strict_types=1);

namespace App\Test\TestCase\Island;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Interact\TestSuite\IslandTestTrait;

/**
 * Exercises catalogue selections through real signed island updates.
 */
class CatalogueIslandTest extends TestCase
{
    use IslandTestTrait;

    /**
     * @var list<string>
     */
    protected array $fixtures = ['app.Packages', 'plugin.Tags.Tags', 'plugin.Tags.Tagged'];

    /**
     * Keep featured selection and cached options deterministic.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Configure::write('Packages.featured', ['markstory/asset_compress']);
        Cache::delete('package_filter_tags_v1');
    }

    /**
     * Pagination hides and restores the featured region without a page reload.
     */
    public function testPagingUpdatesFeaturedRegionAndKeepsItsOrder(): void
    {
        $this->testIsland('Catalogue')
            ->assertSet('paging.sort', 'latest_stable_release_date')
            ->assertSee('data-featured-packages-slider')
            ->set('paging.page', 2)
            ->assertDontSee('data-featured-packages-slider')
            ->set('paging.page', 1)
            ->assertSee('data-featured-packages-slider')
            ->assertSet('featuredOrder', ['markstory/asset_compress']);
    }

    /**
     * A new search resets the page and returns only matching results.
     */
    public function testSearchResetsPageAndRemovesFeaturedPackages(): void
    {
        $this->testIsland('Catalogue')
            ->set('paging.page', 2)
            ->set('search', 'package-02')
            ->assertSet('paging.page', 1)
            ->assertSee('vendor/package-02')
            ->assertDontSee('data-featured-packages-slider')
            ->assertDontSee('vendor/package-03');
    }

    /**
     * Reloadable legacy query keys initialize the declared state.
     */
    public function testInitialUrlSelectionAndGeneratedLinks(): void
    {
        $request = (new ServerRequest(['url' => 'http://localhost/?sort=downloads&direction=desc&page=2']))->withQueryParams([
            'sort' => 'downloads', 'direction' => 'desc', 'page' => '2',
        ]);
        $island = $this->testIsland('Catalogue', request: $request)
            ->assertSet('paging.page', 2)
            ->assertSet('paging.sort', 'downloads');
        $this->assertStringNotContainsString('/_interact/update?', $island->result()->html);
    }

    /**
     * New filters must not reset an explicitly restored history page.
     */
    public function testExplicitPageIsRespectedWhenRestoringSearch(): void
    {
        $this->testIsland('Catalogue')
            ->fill(['search' => 'package', 'paging.page' => 2])
            ->assertSet('paging.page', 2);
    }

    /**
     * Empty and stale pages are bounded by the native island paginator.
     */
    public function testOutOfBoundsPageIsNormalized(): void
    {
        $this->testIsland('Catalogue')->set('paging.page', 99)->assertSet('paging.page', 2);
        $this->testIsland('Catalogue')->set('search', 'no-matching-packages')->assertSet('paging.page', 1);
    }

    /**
     * Browser-callable actions cannot select arbitrary properties.
     */
    public function testRejectsUnknownVersionGroup(): void
    {
        $island = $this->testIsland('Catalogue');
        $this->expectException(BadRequestException::class);
        $island->call('toggleVersion', 'featuredOrder', 'cakephp-5-4');
    }
}
