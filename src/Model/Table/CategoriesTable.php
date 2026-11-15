<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Collection\CollectionInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;

/**
 * Categories imported from Awesome CakePHP.
 *
 * @extends \Cake\ORM\Table<array{}, \App\Model\Entity\Category>
 * @property \Cake\ORM\Association\BelongsToMany<\App\Model\Table\PackagesTable> $Packages
 */
class CategoriesTable extends Table
{
    use CatalogueCacheTrait;

    /**
     * @param array<string, mixed> $config Table configuration.
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setDisplayField('name');
        $this->belongsToMany('Packages');
    }

    /**
     * Category names and unique package counts, keyed by slug for catalogue controls.
     *
     * @param \Cake\ORM\Query\SelectQuery<array<string, mixed>> $query Category query.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findCatalogueOptions(SelectQuery $query, ?string $namespace = null): SelectQuery
    {
        $query->select(['Categories.slug', 'Categories.name', 'package_count' => $query->func()->count('CategoryMemberships.package_id')])
            ->leftJoin(['CategoryMemberships' => 'categories_packages'], ['CategoryMemberships.category_id = Categories.id'])
            ->groupBy(['Categories.id', 'Categories.slug', 'Categories.name'])
            ->orderByAsc('Categories.name');
        if ($namespace !== null) {
            $query->innerJoin(['CataloguePackages' => 'packages'], ['CataloguePackages.id = CategoryMemberships.package_id'])
                ->where(['CataloguePackages.namespace' => $namespace]);
        }
        $query->formatResults(static fn(CollectionInterface $results): CollectionInterface => $results->combine(
            'slug',
            static fn(array|EntityInterface $row): array => ['name' => $row['name'], 'count' => (int)$row['package_count']],
        ));

        return $this->cachePublicQuery($query, 'categories_v2');
    }
}
