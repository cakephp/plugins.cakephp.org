<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Table\PackagesTable;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Http\Client;
use RuntimeException;

/**
 * Updates local curation only after the complete upstream list has been parsed.
 */
class AwesomeListImporter
{
    public const string SOURCE_URL = 'https://github.com/FriendsOfCake/awesome-cakephp';

    public const string README_URL = 'https://raw.githubusercontent.com/FriendsOfCake/awesome-cakephp/master/README.md';

    /**
     * Injectable collaborators keep imports testable without network requests.
     */
    public function __construct(
        private readonly PackagesTable $packages,
        private readonly Client $client,
        private readonly AwesomeListParser $parser,
    ) {
    }

    /**
     * @return array{matched: int, unmatched: list<string>}
     */
    public function sync(): array
    {
        $response = $this->client->get(self::README_URL, [], ['timeout' => 15]);
        if (!$response->isOk()) {
            throw new RuntimeException('Unable to fetch Awesome CakePHP (HTTP ' . $response->getStatusCode() . ').');
        }

        return $this->import($response->getStringBody(), (array)Configure::read('Packages.awesomeAliases', []));
    }

    /**
     * @param string $markdown Complete upstream README.
     * @param array<string, string> $aliases Repository URLs mapped to exact Composer package names.
     * @return array{matched: int, unmatched: list<string>}
     */
    public function import(string $markdown, array $aliases = []): array
    {
        $entries = $this->parser->parse($markdown);
        $byRepository = [];
        $byName = [];
        foreach ($this->packages->find()->select(['id', 'package', 'repo_url'])->all() as $package) {
            $byName[$package->package] = $package->id;
            $repository = $this->parser->repository($package->repo_url);
            if ($repository !== null) {
                $byRepository[$repository][] = $package->id;
            }
        }
        $overrides = [];
        foreach ($aliases as $url => $name) {
            $repository = $this->parser->repository($url);
            if ($repository !== null) {
                $overrides[$repository] = $name;
            }
        }
        $matches = [];
        $matched = [];
        $unmatched = [];
        foreach ($entries as $slug => $entry) {
            foreach ($entry['repositories'] as $repository) {
                $ids = isset($overrides[$repository])
                    ? array_filter([$byName[$overrides[$repository]] ?? null])
                    : ($byRepository[$repository] ?? []);
                if (count($ids) !== 1) {
                    $unmatched[$repository] = true;
                    continue;
                }
                $id = reset($ids);
                $matches[$slug][$id] = true;
                $matched[$id] = true;
            }
        }

        $categories = $this->packages->Categories->getTarget();
        $junction = $this->packages->Categories->junction();
        $this->packages->getConnection()->transactional(function () use ($entries, $matches, $categories, $junction): void {
            $junction->deleteAll([]);
            $active = [];
            foreach ($matches as $slug => $ids) {
                $category = $categories->find()->where(['slug' => $slug])->first() ?? $categories->newEmptyEntity();
                $category->patch(['name' => $entries[$slug]['name'], 'slug' => $slug]);
                $categories->saveOrFail($category);
                $active[] = $category->get('id');
                foreach (array_keys($ids) as $id) {
                    $junction->getConnection()->insert('categories_packages', ['category_id' => $category->get('id'), 'package_id' => $id]);
                }
            }
            $categories->deleteAll($active === [] ? [] : ['id NOT IN' => $active]);
        });
        Cache::clear('catalogue');

        return ['matched' => count($matched), 'unmatched' => array_keys($unmatched)];
    }
}
