<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\ORM\Query\SelectQuery;

/**
 * Packages Controller
 *
 * @property \App\Model\Table\PackagesTable $Packages
 */
class PackagesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // Add default sort if no sort is provided
        $queryParams = $this->request->getQueryParams();
        if (empty($queryParams['sort'])) {
            $this->request = $this->request->withQueryParams(array_merge(
                $queryParams,
                ['sort' => 'downloads', 'direction' => 'desc'],
            ));
        }

        $featuredPackages = [];
        $activeFilterKeys = ['search', 'cakephp_slugs', 'php_slugs'];
        $hasActiveFilters = false;
        foreach ($activeFilterKeys as $key) {
            if ($this->hasActiveFilterValue($queryParams[$key] ?? null)) {
                $hasActiveFilters = true;
                break;
            }
        }
        $currentPage = max(1, (int)($queryParams['page'] ?? 1));
        $showFeaturedPackages = !$hasActiveFilters && $currentPage === 1;
        $featuredPackageNames = [];

        if ($showFeaturedPackages) {
            $featuredPackageNames = array_values(array_filter((array)Configure::read('Packages.featured', [])));
            if ($featuredPackageNames !== []) {
                shuffle($featuredPackageNames);
            }

            $featuredPackages = $this->Packages
                ->find()
                ->contain(['Tags' => function (SelectQuery $q) {
                    return $q->orderByDesc('Tags.label');
                }])
                ->where(['Packages.package IN' => $featuredPackageNames])
                ->all()
                ->indexBy('package')
                ->toArray();

            $featuredPackages = array_values(array_filter(
                array_map(
                    static fn(string $packageName) => $featuredPackages[$packageName] ?? null,
                    $featuredPackageNames,
                ),
            ));
        }

        $query = $this->Packages
            ->find('search', search: $this->request->getQueryParams())
            ->contain(['Tags' => function (SelectQuery $q) {
                return $q->orderByDesc('Tags.label');
            }]);
        if ($featuredPackageNames !== []) {
            $query->where(['Packages.package NOT IN' => $featuredPackageNames]);
        }
        $packages = $this->paginate($query, ['limit' => 21]);

        $cakephpTags = $this->Packages->Tags->find('list', keyField: 'slug')
            ->where(['slug LIKE' => 'cakephp-%'])
            ->toArray();
        $cakephpTags = $this->sortVersionTags($cakephpTags, 'CakePHP');
        $phpTags = $this->Packages->Tags->find('list', keyField: 'slug')
            ->where(['slug LIKE' => 'php-%'])
            ->toArray();
        $phpTags = $this->sortVersionTags($phpTags, 'PHP');

        $this->set(compact('featuredPackages', 'packages', 'cakephpTags', 'phpTags'));
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function hasActiveFilterValue(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->hasActiveFilterValue($item)) {
                    return true;
                }
            }

            return false;
        }

        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return (bool)$value;
    }

    /**
     * @param array<string, string> $tags
     * @return array<string, string>
     */
    protected function sortVersionTags(array $tags, string $prefix): array
    {
        $pattern = '/^' . preg_quote($prefix, '/') . ':\s*/';
        uasort($tags, static function (string $left, string $right) use ($pattern): int {
            $leftVersion = preg_replace($pattern, '', $left) ?: $left;
            $rightVersion = preg_replace($pattern, '', $right) ?: $right;

            return version_compare($rightVersion, $leftVersion);
        });

        return $tags;
    }
}
