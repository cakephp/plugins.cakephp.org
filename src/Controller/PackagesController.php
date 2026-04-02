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

        $featuredPackageNames = array_values(array_filter((array)Configure::read('Packages.featured', [])));
        $featuredPackages = [];

        if ($featuredPackageNames !== []) {
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
                    static fn (string $packageName) => $featuredPackages[$packageName] ?? null,
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
            ->orderByAsc('label')
            ->toArray();
        $phpTags = $this->Packages->Tags->find('list', keyField: 'slug')
            ->where(['slug LIKE' => 'php-%'])
            ->orderByAsc('label')
            ->toArray();

        $this->set(compact('featuredPackages', 'packages', 'cakephpTags', 'phpTags'));
    }
}
