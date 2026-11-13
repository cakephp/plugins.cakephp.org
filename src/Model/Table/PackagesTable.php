<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Filter\PackagesCollection;
use Cake\Cache\Cache;
use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Packages Model
 *
 * @method \App\Model\Entity\Package newEmptyEntity()
 * @method \App\Model\Entity\Package newEntity(array<mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Package> newEntities(array<mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Package get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Package findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Package patchEntity(\App\Model\Entity\Package $entity, array<mixed> $data, array<string, mixed> $options = [])
 * @method array<\App\Model\Entity\Package> patchEntities(iterable<\App\Model\Entity\Package> $entities, array<mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Package|false save(\App\Model\Entity\Package $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\Package saveOrFail(\App\Model\Entity\Package $entity, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\App\Model\Entity\Package>|false saveMany(iterable<\App\Model\Entity\Package> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\App\Model\Entity\Package> saveManyOrFail(iterable<\App\Model\Entity\Package> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\App\Model\Entity\Package>|false deleteMany(iterable<\App\Model\Entity\Package> $entities, array<string, mixed> $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\App\Model\Entity\Package> deleteManyOrFail(iterable<\App\Model\Entity\Package> $entities, array<string, mixed> $options = [])
 * @property \Cake\ORM\Association\HasMany<\Tags\Model\Table\TaggedTable> $Tagged
 * @property \Cake\ORM\Association\BelongsToMany<\Tags\Model\Table\TagsTable> $Tags
 * @property \Cake\ORM\Association\BelongsToMany<\App\Model\Table\CategoriesTable> $Categories
 * @mixin \Search\Model\Behavior\SearchBehavior
 * @mixin \Tags\Model\Behavior\TagBehavior
 * @extends \Cake\ORM\Table<array{Search: \Search\Model\Behavior\SearchBehavior, Tag: \Tags\Model\Behavior\TagBehavior}, \App\Model\Entity\Package>
 * @method \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Package> find(string $type = 'all', mixed ...$args)
 */
class PackagesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('packages');
        $this->setDisplayField('package');
        $this->setPrimaryKey('id');

        $this->addBehavior('Search.Search', [
            'collectionClass' => PackagesCollection::class,
        ]);
        $this->addBehavior('Tags.Tag', ['taggedCounter' => false]);
        $this->belongsToMany('Categories')->setSort(['Categories.name' => 'ASC']);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('package')
            ->maxLength('package', 255)
            ->requirePresence('package', 'create')
            ->notEmptyString('package');

        $validator
            ->scalar('description')
            ->requirePresence('description', 'create')
            ->allowEmptyString('description');

        $validator
            ->scalar('repo_url')
            ->maxLength('repo_url', 255)
            ->requirePresence('repo_url', 'create')
            ->notEmptyString('repo_url');

        $validator
            ->integer('downloads')
            ->requirePresence('downloads', 'create')
            ->notEmptyString('downloads');

        $validator
            ->integer('stars')
            ->requirePresence('stars', 'create')
            ->notEmptyString('stars');

        $validator
            ->scalar('latest_stable_version')
            ->requirePresence('latest_stable_version', 'create')
            ->allowEmptyString('latest_stable_version');

        $validator
            ->date('latest_stable_release_date')
            ->requirePresence('latest_stable_release_date', 'create')
            ->allowEmptyDate('latest_stable_release_date');

        return $validator;
    }

    /**
     * Build the public catalogue with its version associations.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Package> $query Query instance.
     * @param array<string, mixed> $filters Accepted search and version filters.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Package>
     */
    public function findCatalogue(SelectQuery $query, array $filters, ?string $namespace = null): SelectQuery
    {
        if ($namespace !== null) {
            $query->find('namespace', namespace: $namespace);
        }
        $query->find('search', search: $filters)
            ->contain(['Categories', 'Tags' => static fn(SelectQuery $tags) => $tags->orderByDesc('Tags.label')]);
        if (!empty($filters['category']) || !empty($filters['awesome'])) {
            $junction = $this->Categories->junction();
            $alias = $junction->getAlias();
            $membership = $junction->find()->select([$alias . '.package_id'])
                ->where(static fn(QueryExpression $exp) => $exp->equalFields($alias . '.package_id', 'Packages.id'));
            if (!empty($filters['category'])) {
                $membership->innerJoin(['CurationCategory' => 'categories'], ['CurationCategory.id = ' . $alias . '.category_id'])
                    ->where(['CurationCategory.slug' => $filters['category']]);
            }
            $query->where(static fn(QueryExpression $exp) => $exp->exists($membership));
        }

        return $query;
    }

    /**
     * Match a literal Composer vendor prefix, including namespaces containing underscores.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Package> $query Package query.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Package>
     */
    public function findNamespace(SelectQuery $query, string $namespace): SelectQuery
    {
        $prefix = strtr($namespace, [
            '!' => '!!',
            '%' => '!%',
            '_' => '!_',
        ]) . '/%';

        // Expression-owned bindings survive the ORM's eager-loading subqueries.
        return $query->where(new QueryExpression([
            $query->expr()->like($this->aliasField('package'), $prefix),
            "ESCAPE '!'",
        ], [], ''));
    }

    /**
     * Hero totals cover the namespace before any interactive filters are applied.
     *
     * @return array{plugins: int, downloads: int, stars: int}
     */
    public function namespaceStats(string $namespace): array
    {
        $query = $this->find('namespace', namespace: $namespace);
        $totals = $query->select([
            'plugins' => $query->func()->count('*'),
            'downloads' => $query->func()->sum('Packages.downloads'),
            'stars' => $query->func()->sum('Packages.stars'),
        ])->first();

        return [
            'plugins' => (int)($totals?->get('plugins') ?? 0),
            'downloads' => (int)($totals?->get('downloads') ?? 0),
            'stars' => (int)($totals?->get('stars') ?? 0),
        ];
    }

    /**
     * Load configured featured packages without changing their display order.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Package> $query Query instance.
     * @param list<string> $names Configured package names.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Package>
     */
    public function findFeatured(SelectQuery $query, array $names): SelectQuery
    {
        return $query->where(['Packages.package IN' => $names])
            ->contain(['Categories', 'Tags' => static fn(SelectQuery $tags) => $tags->orderByDesc('Tags.label')]);
    }

    /**
     * Search canonical package names for the featured editor's picker.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Package> $query Package query.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Package>
     */
    public function findFeaturedCandidates(SelectQuery $query, string $search = ''): SelectQuery
    {
        return $query->select(['id', 'package'])
            ->where(['package LIKE' => '%' . trim($search) . '%'])
            ->orderByAsc('package');
    }

    /**
     * @return array<string, array{name: string, count: int}> Categories and counts within the current catalogue scope.
     */
    public function categoryOptions(?string $namespace = null): array
    {
        $query = $this->Categories->find();
        $query->select(['Categories.slug', 'Categories.name', 'package_count' => $query->func()->count('CategoryMemberships.package_id')])
            ->leftJoin(['CategoryMemberships' => 'categories_packages'], ['CategoryMemberships.category_id = Categories.id'])
            ->groupBy(['Categories.id', 'Categories.slug', 'Categories.name'])
            ->orderByAsc('Categories.name');
        if ($namespace !== null) {
            $query->where([
                'CategoryMemberships.package_id IN' => $this->find('namespace', namespace: $namespace)->select(['id']),
            ]);
        }
        $options = [];
        foreach ($query->all() as $category) {
            $options[$category->get('slug')] = ['name' => $category->get('name'), 'count' => (int)$category->get('package_count')];
        }

        return $options;
    }

    /**
     * Cache version choices in descending semantic-version order.
     *
     * @return array{cakephp: array<string, string>, php: array<string, string>}
     */
    public function filterOptions(): array
    {
        return Cache::remember('package_filter_tags_v1', function (): array {
            $options = [];
            foreach (['cakephp' => 'CakePHP', 'php' => 'PHP'] as $key => $label) {
                /** @var array<string, string> $tags Scalar list results, keyed by version slug. */
                $tags = $this->Tags->find('list', keyField: 'slug', valueField: 'label')
                    ->where(['slug LIKE' => $key . '-%'])->toArray();
                $pattern = '/^' . preg_quote($label, '/') . ':\\s*/';
                uasort($tags, static fn(string $a, string $b): int => version_compare(
                    preg_replace($pattern, '', $b) ?? $b,
                    preg_replace($pattern, '', $a) ?? $a,
                ));
                $options[$key] = $tags;
            }

            return $options;
        });
    }
}
