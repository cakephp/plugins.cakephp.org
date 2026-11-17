<?php
declare(strict_types=1);

namespace App\Test\TestCase\Island;

use Cake\Cache\Cache;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use DOMDocument;
use DOMXPath;
use Interact\TestSuite\IslandTestTrait;
use InvalidArgumentException;
use Override;
use Tags\Model\Table\TaggedTable;
use Tags\Model\Table\TagsTable;

/**
 * Exercises catalogue selections through real signed island updates.
 */
class CatalogueIslandTest extends TestCase
{
    use IslandTestTrait;

    /**
     * @var list<string>
     */
    #[Override]
    protected array $fixtures = [
        'app.FeaturedPlugins', 'app.Packages', 'app.Categories', 'app.CategoriesPackages',
        'app.Keywords', 'app.KeywordsPackages', 'plugin.Tags.Tags', 'plugin.Tags.Tagged',
    ];

    /**
     * Keyword selection retains other filters and sorting, resets paging and clears independently.
     */
    public function testKeywordSelectionAndClearing(): void
    {
        $this->testIsland('Catalogue')->set('paging.page', 2)
            ->call('selectKeyword', ' SASS ')->assertSet('keyword', 'sass')->assertSet('paging.page', 1)
            ->assertSee('markstory/asset_compress')->assertDontSee('cakedc/users')
            ->call('selectCategory', 'asset-management')->call('toggleAwesome')
            ->set('paging.sort', 'stars')->call('selectKeyword', '')
            ->assertSet('category', 'asset-management')->assertSet('awesomeOnly', true)->assertSet('paging.sort', 'stars')
            ->call('selectKeyword', 'sass')->call('clearFilters')
            ->assertSet('keyword', '')->assertSet('category', '')->assertSet('awesomeOnly', false)->assertSet('paging.sort', 'stars');
    }

    /**
     * Exact keyword URLs preserve unknown terms rather than silently broadening results.
     */
    public function testKeywordUrlsAndNamespaceScope(): void
    {
        $request = new ServerRequest(['url' => '/?keyword=OAuth2']);
        $this->testIsland('Catalogue', request: $request)->assertSet('keyword', 'oauth2')
            ->assertSee('cakedc/users')->assertSee('Keyword: oauth2');
        $this->testIsland('Catalogue', ['namespace' => 'vendor'])->call('selectKeyword', 'sass')
            ->assertSet('namespace', 'vendor')->assertSet('keyword', 'sass')->assertSee('No plugins found');
        $this->testIsland('Catalogue')->call('selectKeyword', 'unknown-term')
            ->assertSee('Keyword: unknown-term')->assertSee('No plugins found');
    }

    /**
     * Bound keyword input like the existing free-text search.
     */
    public function testOversizedKeywordIsRejected(): void
    {
        $island = $this->testIsland('Catalogue');
        $this->expectException(BadRequestException::class);
        $island->call('selectKeyword', str_repeat('a', 192));
    }

    /**
     * Category and membership changes are live, reloadable and reset pagination.
     */
    public function testCategoryAndMembershipActions(): void
    {
        $this->testIsland('Catalogue')
            ->set('paging.page', 2)
            ->call('toggleAwesome')
            ->assertSet('awesomeOnly', true)
            ->assertSet('paging.page', 1)
            ->assertSee('cakedc/users')
            ->assertDontSee('vendor/package-02')
            ->call('selectCategory', 'asset-management')
            ->assertSet('category', 'asset-management')
            ->assertSee('markstory/asset_compress')
            ->assertDontSee('cakedc/users')
            ->set('search', 'users')
            ->assertSee('No plugins found')
            ->call('clearFilters')
            ->assertSet('awesomeOnly', false)
            ->assertSet('category', '')
            ->assertSee('cakedc/users');
    }

    /**
     * Invalid categories normalize to the full directory.
     */
    public function testUnknownCategoryIsCleared(): void
    {
        $this->testIsland('Catalogue')->call('selectCategory', 'not-a-category')->assertSet('category', '');
    }

    /**
     * Version actions cycle all three states, reset paging and allow explicit detail selection.
     */
    public function testVersionExclusionCycle(): void
    {
        $tags = $this->fetchTable(TagsTable::class);
        $tag = $tags->saveOrFail($tags->newEntity(['slug' => 'cakephp-4-4', 'label' => 'CakePHP: 4.4']));
        $tagged = $this->fetchTable(TaggedTable::class);
        $tagged->saveOrFail($tagged->newEntity(['tag_id' => $tag->id, 'fk_id' => 25, 'fk_model' => 'Packages']));
        $this->testIsland('Catalogue')->set('paging.page', 2)
            ->call('toggleVersion', 'cakephp_slugs', 'cakephp-4-4')
            ->assertSet('cakephpSlugs', ['cakephp-4-4'])->assertSet('excludedCakephpSlugs', [])->assertSet('paging.page', 1)
            ->assertSee('cakedc/users')
            ->call('toggleVersion', 'cakephp_slugs', 'cakephp-4-4')
            ->assertSet('cakephpSlugs', [])->assertSet('excludedCakephpSlugs', ['cakephp-4-4'])
            ->assertSee('Not CakePHP 4.4')->assertDontSee('cakedc/users')
            ->call('toggleVersion', 'cakephp_slugs', 'cakephp-4-4')
            ->assertSet('cakephpSlugs', [])->assertSet('excludedCakephpSlugs', [])
            ->set('excludedCakephpSlugs', ['cakephp-4-4'])
            ->call('selectVersion', 'cakephp_slugs', 'cakephp-4-4')
            ->assertSet('cakephpSlugs', ['cakephp-4-4'])->assertSet('excludedCakephpSlugs', [])
            ->call('clearVersions', 'cakephp_slugs')->assertSet('cakephpSlugs', []);
    }

    /**
     * Category exclusions combine with a single included category and reset together.
     */
    public function testCategoryExclusionCycle(): void
    {
        $this->testIsland('Catalogue')
            ->call('toggleCategory', 'asset-management')->assertSet('category', 'asset-management')
            ->call('toggleCategory', 'asset-management')->assertSet('category', '')->assertSet('excludedCategories', ['asset-management'])
            ->assertDontSee('markstory/asset_compress')->assertSee('Not Asset Management')
            ->call('toggleCategory', 'authentication-authorization')->assertSet('category', 'authentication-authorization')
            ->call('toggleCategory', 'authentication-authorization')->assertSet('category', '')
            ->assertSet('excludedCategories', ['asset-management', 'authentication-authorization'])
            ->call('toggleCategory', 'asset-management')->assertSet('excludedCategories', ['authentication-authorization'])
            ->call('selectCategory', 'authentication-authorization')->assertSet('category', 'authentication-authorization')->assertSet('excludedCategories', [])
            ->set('excludedCategories', ['asset-management'])->call('clearFilters')->assertSet('category', '')->assertSet('excludedCategories', []);
    }

    /**
     * URL exclusions are whitelisted, deduplicated and take precedence over conflicting inclusions.
     */
    public function testInitialExclusionsAreNormalizedAndReloadable(): void
    {
        $request = new ServerRequest(['url' => '/?' . http_build_query([
            'category' => 'asset-management',
            'exclude_categories' => ['asset-management', 'asset-management', 'not-a-category'],
            'exclude_php_slugs' => ['unknown'],
            'sort' => 'stars',
        ])]);
        $this->testIsland('Catalogue', request: $request)
            ->assertSet('category', '')->assertSet('excludedCategories', ['asset-management'])->assertSet('excludedPhpSlugs', [])
            ->assertSet('paging.sort', 'stars')->assertSee('exclude_categories')->assertSee('Reset filters')
            ->call('clearFilters')->assertSet('excludedCategories', [])->assertSet('excludedCakephpSlugs', [])->assertSet('excludedPhpSlugs', [])
            ->assertSet('paging.sort', 'stars');
    }

    /**
     * Paging, filtering and resetting retain the server-owned namespace scope.
     */
    public function testNamespaceScopeSurvivesUpdates(): void
    {
        $request = new ServerRequest(['url' => 'http://localhost/plugins/vendor']);
        $island = $this->testIsland('Catalogue', ['namespace' => 'vendor'], request: $request)
            ->assertDontSee('data-featured-plugins')
            ->assertDontSee('markstory/asset_compress')
            ->set('paging.page', 2)
            ->assertSet('paging.page', 2)
            ->set('search', 'package-01')
            ->assertSet('paging.page', 1)
            ->assertSee('vendor/package-01')
            ->assertDontSee('vendor/package-02')
            ->call('clearFilters')
            ->assertSet('namespace', 'vendor')
            ->assertDontSee('data-featured-plugins')
            ->assertDontSee('markstory/asset_compress');
        $this->assertStringContainsString('/plugins/vendor?', $island->result()->html);
        $this->assertStringNotContainsString('/_interact/update?', $island->result()->html);
    }

    /**
     * Namespaced category choices exclude unrelated categories and remain stable while searching.
     */
    public function testNamespaceCategoryChoices(): void
    {
        $this->testIsland('Catalogue', ['namespace' => 'markstory'])
            ->assertSee('Asset Management')
            ->assertDontSee('Authentication & Authorization')
            ->set('search', 'no-match')
            ->assertSee('Asset Management')
            ->assertSee('No plugins found');
    }

    /**
     * Browser updates cannot replace the namespace supplied by the hosting page.
     */
    public function testNamespaceCannotBeChangedByUpdates(): void
    {
        $island = $this->testIsland('Catalogue', ['namespace' => 'vendor']);
        $this->expectException(InvalidArgumentException::class);
        $island->set('namespace', 'cakedc');
    }

    /**
     * Keep cached compatibility options deterministic.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Cache::delete('package_filter_tags_v1');
    }

    /**
     * Pagination keeps the catalogue free of a separate featured region.
     */
    public function testPagingKeepsResultsInOneList(): void
    {
        $this->testIsland('Catalogue')
            ->assertSet('paging.sort', 'latest_stable_release_date')
            ->assertDontSee('data-featured-plugins')
            ->set('paging.page', 2)
            ->assertDontSee('data-featured-plugins')
            ->set('paging.page', 1)
            ->assertDontSee('data-featured-plugins');
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
            ->assertDontSee('data-featured-plugins')
            ->assertDontSee('vendor/package-03');
    }

    /**
     * Reloadable legacy query keys initialize the declared state.
     */
    public function testInitialUrlSelectionAndGeneratedLinks(): void
    {
        $request = new ServerRequest(['url' => 'http://localhost/?sort=downloads&direction=desc&page=2'])->withQueryParams([
            'sort' => 'downloads', 'direction' => 'desc', 'page' => '2',
        ]);
        $island = $this->testIsland('Catalogue', request: $request)
            ->assertSet('paging.page', 2)
            ->assertSet('paging.sort', 'downloads');
        $this->assertStringNotContainsString('/_interact/update?', $island->result()->html);
    }

    /**
     * Generated reset URLs retain the selected sort without double-escaping ampersands.
     */
    public function testResetLinkQueryEncoding(): void
    {
        $island = $this->testIsland('Catalogue')->fill([
            'search' => 'asset', 'category' => 'asset-management',
            'paging.sort' => 'downloads', 'paging.direction' => 'asc',
        ]);
        $links = $this->document($island->result()->html)->query('//a[@data-clear-catalogue or @data-category-slug=""]/@href');
        $this->assertNotFalse($links);
        $this->assertGreaterThan(0, $links->length);
        foreach ($links as $link) {
            $this->assertNotNull($link->nodeValue);
            parse_str((string)parse_url($link->nodeValue, PHP_URL_QUERY), $params);
            $this->assertSame('downloads', $params['sort']);
            $this->assertSame('asc', $params['direction']);
            foreach (array_keys($params) as $key) {
                $this->assertStringStartsNotWith('amp;', (string)$key);
            }
        }
    }

    /**
     * The teaser metric is server-rendered from the selected sort in both catalogues.
     */
    public function testTeaserMetricFollowsSort(): void
    {
        foreach ([null, 'vendor'] as $namespace) {
            $island = $this->testIsland('Catalogue', ['namespace' => $namespace]);
            $cases = [
                ['monthly_downloads', 'desc', 'Monthly downloads', $namespace === null ? '2,000' : '98'],
                ['monthly_downloads', 'asc', 'Monthly downloads', '76'],
                ['stars', 'desc', 'Stars', $namespace === null ? '500' : '98'],
                ['stars', 'asc', 'Stars', '76'],
                ['downloads', 'desc', 'Downloads', $namespace === null ? '20,000' : '998'],
            ];
            foreach ($cases as [$sort, $direction, $label, $value]) {
                $island->fill(['paging.sort' => $sort, 'paging.direction' => $direction]);
                $document = $this->document($island->result()->html);
                $metric = '(//*[@data-package-row])[1]//dl[@class="package-meta"]';
                $this->assertSame($label, $document->evaluate('string(' . $metric . '//dt)'));
                $this->assertSame($value, $document->evaluate('string(' . $metric . '//dd)'));
            }
        }
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

    /**
     * Resetting filters preserves the chosen sort and restores the full directory.
     */
    public function testClearFiltersRetainsSort(): void
    {
        $this->testIsland('Catalogue')
            ->fill(['search' => 'asset', 'category' => 'asset-management', 'awesomeOnly' => true, 'paging.sort' => 'downloads'])
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('cakephpSlugs', [])
            ->assertSet('phpSlugs', [])
            ->assertSet('category', '')
            ->assertSet('awesomeOnly', false)
            ->assertSet('paging.page', 1)
            ->assertSet('paging.sort', 'downloads');
    }

    /**
     * A version chosen in details replaces its family while retaining search and sort.
     */
    public function testDetailVersionRetainsOtherSelections(): void
    {
        $tags = $this->fetchTable(TagsTable::class);
        $tag = $tags->saveOrFail($tags->newEntity(['slug' => 'cakephp-4-4', 'label' => 'CakePHP: 4.4']));
        $tagged = $this->fetchTable(TaggedTable::class);
        $tagged->saveOrFail($tagged->newEntity(['tag_id' => $tag->id, 'fk_id' => 1, 'fk_model' => 'Packages']));
        $this->testIsland('Catalogue')
            ->fill(['search' => 'asset', 'paging.sort' => 'downloads'])
            ->call('selectVersion', 'cakephp_slugs', 'cakephp-4-4')
            ->assertSet('cakephpSlugs', ['cakephp-4-4'])
            ->assertSet('search', 'asset')
            ->assertSet('paging.sort', 'downloads')
            ->assertSet('paging.page', 1);
    }

    /**
     * The update transport cannot bypass the input's search-length bound.
     */
    public function testOverlongSearchIsRejected(): void
    {
        $island = $this->testIsland('Catalogue');
        $this->expectException(BadRequestException::class);
        $island->set('search', str_repeat('a', 101));
    }

    /**
     * Parse rendered island HTML without executing JavaScript.
     */
    private function document(string $html): DOMXPath
    {
        $document = new DOMDocument();
        $document->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);

        return new DOMXPath($document);
    }
}
