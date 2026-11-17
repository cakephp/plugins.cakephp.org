<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Table\PackagesTable;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Override;
use SimpleXMLElement;

/**
 * Crawlers receive canonical, discoverable URLs without requiring JavaScript.
 */
class SitemapsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    #[Override]
    protected array $fixtures = ['app.Packages'];

    /**
     * The index links to XML documents covering all canonical public pages.
     */
    public function testPublicSitemap(): void
    {
        $this->get('/sitemap.xml');
        $this->assertResponseOk();
        $this->assertContentType('xml');
        $sitemaps = $this->urls('sitemapindex', 'sitemap');
        $this->assertCount(3, $sitemaps);
        $urls = [];
        foreach ($sitemaps as $sitemap) {
            $this->get((string)parse_url($sitemap, PHP_URL_PATH));
            $this->assertResponseOk();
            $this->assertContentType('xml');
            $urls = array_merge($urls, $this->urls());
        }
        $this->assertCount(33, $urls);
        $this->assertSame($urls, array_values(array_unique($urls)));
        foreach (['/', '/publishers', '/requirements', '/plugins/vendor', '/plugins/markstory/asset_compress', '/plugins/cakedc/users'] as $path) {
            $this->assertContains(Router::url($path, true), $urls);
        }
        foreach ($urls as $url) {
            $this->assertStringStartsWith(Router::fullBaseUrl() . '/', $url);
            $this->assertStringNotContainsString('?', $url);
            $this->assertStringNotContainsString('/admin', $url);
        }
        $this->assertResponseNotContains('<html');
        $this->assertResponseNotContains('<lastmod>');
        $this->assertStringContainsString(
            'Sitemap: https://plugins.cakephp.org/sitemap.xml',
            (string)file_get_contents(WWW_ROOT . 'robots.txt'),
        );
    }

    /**
     * Query parameters on the sitemap itself never create filtered URLs.
     */
    public function testQueryParametersDoNotAffectSitemap(): void
    {
        $this->get('/sitemap-plugins-1.xml?search=users&page=2&sort=stars');
        $this->assertResponseOk();
        $this->assertCount(26, $this->urls());
        $this->assertResponseNotContains('search=');
    }

    /**
     * Canonical routing retains numeric namespaces, dots and underscores.
     */
    public function testCanonicalPackageSegments(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $package = $packages->patchEntity($packages->get(2), ['package' => '123/some_plugin.v2']);
        $packages->saveOrFail($package);
        $this->get('/sitemap-publishers-1.xml');
        $this->assertResponseOk();
        $urls = $this->urls();
        $this->assertContains(Router::url('/plugins/123', true), $urls);
        $this->get('/sitemap-plugins-1.xml');
        $urls = $this->urls();
        $this->assertContains(Router::url('/plugins/123/some_plugin.v2', true), $urls);
    }

    /**
     * Ineligible route segments must not advertise a broken plugin detail link.
     */
    public function testInvalidStoredPackageIsExcluded(): void
    {
        $this->fetchTable(PackagesTable::class)->updateAll([
            'package' => 'invalid/name?query',
            'namespace' => 'invalid',
        ], ['id' => 2]);
        $this->get('/sitemap-plugins-1.xml');
        $this->assertResponseOk();
        $urls = $this->urls();
        $this->assertCount(25, $urls);
        $this->assertResponseNotContains('name?query');
        $this->get('/sitemap-publishers-1.xml');
        $urls = $this->urls();
        $this->assertNotContains(Router::url('/plugins/invalid', true), $urls);
    }

    /**
     * An empty catalogue still exposes the static public entry points.
     */
    public function testEmptyCatalogue(): void
    {
        $this->fetchTable(PackagesTable::class)->deleteAll([]);
        $this->get('/sitemap.xml');
        $this->assertResponseOk();
        $this->assertSame([Router::url('/sitemap-pages-1.xml', true)], $this->urls('sitemapindex', 'sitemap'));
        $this->get('/sitemap-pages-1.xml');
        $this->assertCount(3, $this->urls());
        $this->get('/sitemap-plugins-1.xml');
        $this->assertResponseCode(404);
    }

    /**
     * Crawlers can check the resource with HEAD as well as GET.
     */
    public function testHeadRequest(): void
    {
        $this->head('/sitemap.xml');
        $this->assertResponseOk();
        $this->assertContentType('xml');
        $this->head('/sitemap-plugins-1.xml');
        $this->assertResponseOk();
        $this->assertContentType('xml');
    }

    /**
     * A second page is advertised when required, with no duplicates or missing URLs.
     */
    public function testPagination(): void
    {
        $packages = $this->fetchTable(PackagesTable::class);
        $query = $packages->insertQuery()->insert(['package', 'namespace', 'description', 'repo_url', 'downloads', 'stars']);
        for ($i = 0; $i < 1000; $i++) {
            $query->values([
                'package' => sprintf('publisher-%04d/plugin', $i),
                'namespace' => sprintf('publisher-%04d', $i),
                'description' => 'Sitemap pagination fixture.',
                'repo_url' => '',
                'downloads' => 0,
                'stars' => 0,
            ]);
        }
        $query->execute();
        $this->get('/sitemap.xml');
        $this->assertCount(5, $this->urls('sitemapindex', 'sitemap'));
        foreach (['plugins' => 1026, 'publishers' => 1004] as $type => $count) {
            $this->get('/sitemap-' . $type . '-1.xml');
            $first = $this->urls();
            $this->assertCount(1000, $first);
            $this->get('/sitemap-' . $type . '-2.xml');
            $second = $this->urls();
            $this->assertCount($count - 1000, $second);
            $this->assertCount($count, array_unique(array_merge($first, $second)));
            $this->assertSame([], array_intersect($first, $second));
        }
    }

    /**
     * Empty, invalid or excessive page requests do not create duplicate documents.
     */
    public function testMissingPages(): void
    {
        foreach (['plugins-2', 'publishers-2', 'pages-2', 'plugins-0', 'unknown-1'] as $page) {
            $this->get('/sitemap-' . $page . '.xml');
            $this->assertResponseCode(404);
        }
    }

    /**
     * XML parsing and the namespace check catch encoding or accidental HTML output.
     *
     * @return list<string>
     */
    private function urls(string $root = 'urlset', string $item = 'url'): array
    {
        $this->assertNotNull($this->response);
        $document = new SimpleXMLElement((string)$this->response->getBody());
        $this->assertSame($root, $document->getName());
        $this->assertSame('http://www.sitemaps.org/schemas/sitemap/0.9', $document->getDocNamespaces()[''] ?? null);
        $document->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $locations = $document->xpath('/s:' . $root . '/s:' . $item . '/s:loc');
        $this->assertIsArray($locations);

        return array_values(array_map(static fn(SimpleXMLElement $location): string => (string)$location, $locations));
    }
}
