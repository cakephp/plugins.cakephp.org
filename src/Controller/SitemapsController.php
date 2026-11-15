<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\PackagesTable;
use Cake\Http\Exception\NotFoundException;
use Cake\Routing\Router;
use Cake\View\XmlView;

/**
 * Advertise canonical public pages independently of the JavaScript catalogue.
 */
class SitemapsController extends AppController
{
    private const int PAGE_SIZE = 1000;

    /**
     * Crawlers do not need an authenticated session or search processing.
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Search->setConfig('actions', [], false);
        $this->Authentication->allowUnauthenticated(['index', 'view']);
    }

    /**
     * Advertise only populated sitemap pages.
     */
    public function index(): void
    {
        $sitemaps = [];
        foreach ($this->entries() as $type => $entries) {
            $pages = (int)ceil(count($entries) / self::PAGE_SIZE);
            for ($page = 1; $page <= $pages; $page++) {
                $sitemaps[] = ['loc' => Router::url([
                    '_name' => 'sitemap:view',
                    'type' => $type,
                    'page' => (string)$page,
                ], true)];
            }
        }
        $this->serializeSitemap('sitemapindex', 'sitemap', $sitemaps);
    }

    /**
     * Each document contains at most 1,000 URLs; missing pages return 404.
     */
    public function view(string $type, string $page): void
    {
        $entries = $this->entries()[$type] ?? [];
        $number = filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($number === false || $number > (int)ceil(count($entries) / self::PAGE_SIZE)) {
            throw new NotFoundException('Sitemap page not found.');
        }
        $entries = array_slice($entries, ($number - 1) * self::PAGE_SIZE, self::PAGE_SIZE);
        $urls = array_map(static fn(array $route): array => ['loc' => Router::url($route, true)], $entries);
        $this->serializeSitemap('urlset', 'url', $urls);
    }

    /**
     * Keep ordering stable and reuse cached catalogue data without caching a host.
     *
     * @return array<string, list<array<string, string>>>
     */
    private function entries(): array
    {
        $pages = [];
        foreach (['plugin:index', 'publisher:index', 'page:requirements'] as $name) {
            $pages[] = ['_name' => $name];
        }
        $namespaces = [];
        $plugins = [];
        foreach ($this->fetchTable(PackagesTable::class)->unhydratedFind('sitemap')->all() as $package) {
            $parts = [];
            if (!preg_match('#^([a-z0-9][a-z0-9_.-]*)/([a-z0-9][a-z0-9_.-]*)$#D', strtolower($package['package']), $parts)) {
                continue;
            }
            $plugins[$parts[1] . '/' . $parts[2]] = [
                '_name' => 'plugin:view',
                'namespace' => $parts[1],
                'name' => $parts[2],
            ];
            $namespace = strtolower($package['namespace']);
            if (preg_match('/^[a-z0-9][a-z0-9_.-]*$/D', $namespace)) {
                $namespaces[$namespace] = true;
            }
        }
        ksort($namespaces);
        ksort($plugins);
        $publishers = [];
        foreach (array_keys($namespaces) as $namespace) {
            $publishers[] = [
                '_name' => 'plugin:namespace',
                'namespace' => (string)$namespace,
            ];
        }

        return ['pages' => $pages, 'plugins' => array_values($plugins), 'publishers' => $publishers];
    }

    /**
     * Both sitemap formats use CakePHP's XML serialization and escaping.
     *
     * @param string $root Document root.
     * @param string $item Entry element name.
     * @param list<array{loc: string}> $entries Absolute URLs.
     */
    private function serializeSitemap(string $root, string $item, array $entries): void
    {
        $this->set('sitemap', [
            $root => [
                '@xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
                $item => $entries,
            ],
        ]);
        $this->viewBuilder()
            ->setClassName(XmlView::class)
            ->setOption('serialize', 'sitemap');
    }
}
