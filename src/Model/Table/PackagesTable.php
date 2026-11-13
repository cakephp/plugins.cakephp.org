<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Filter\PackagesCollection;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Database\Expression\FunctionExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
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
            ->nonNegativeInteger('monthly_downloads')
            ->allowEmptyString('monthly_downloads');

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
     * Derive the indexed vendor whenever an import creates or renames a package.
     *
     * @param \Cake\Event\EventInterface $event Save event.
     * @param \Cake\Datasource\EntityInterface $entity Package being saved.
     * @param \ArrayObject<string, mixed> $options Save options.
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if ($entity->isNew() || $entity->isDirty('package')) {
            $entity->set('namespace', strtolower(explode('/', (string)$entity->get('package'), 2)[0]));
        }
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
     * Match the indexed Composer namespace exactly.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Package> $query Package query.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\Package>
     */
    public function findNamespace(SelectQuery $query, string $namespace): SelectQuery
    {
        return $query->where([
            $this->aliasField('namespace') => $namespace,
        ]);
    }

    /**
     * Rank Composer namespaces from packages supporting the selected CakePHP major.
     *
     * @param \Cake\ORM\Query\SelectQuery $query Package query.
     * @param string $search Case-insensitive namespace substring.
     * @param string $sort Popularity metric.
     * @param string|null $major CakePHP major; null includes all versions.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findPublishers(SelectQuery $query, string $search = '', string $sort = 'monthly_downloads', ?string $major = null): SelectQuery
    {
        $query->select([
            'namespace' => $this->aliasField('namespace'),
            'plugins' => $query->func()->count('*'),
            'downloads' => $query->func()->sum($this->aliasField('downloads')),
            'monthly_downloads' => $query->expr()->case()
                ->when($query->expr()->eq($query->func()->count($this->aliasField('monthly_downloads')), $query->func()->count('*')))
                ->then($query->func()->sum($this->aliasField('monthly_downloads'))),
            'stars' => $query->func()->sum($this->aliasField('stars')),
            'latest_release' => $query->func()->max($this->aliasField('latest_stable_release_date'))->setReturnType('string'),
        ])->where([
            $this->aliasField('namespace') . ' !=' => '',
        ])->groupBy($this->aliasField('namespace'));

        if ($major !== null) {
            $query->where($query->expr()->exists($this->publisherCompatibility($major)));
        }

        $search = mb_strtolower(trim($search));
        if ($search !== '') {
            $query->where($query->expr()->gt(new FunctionExpression('INSTR', [
                $this->aliasField('namespace') => 'identifier',
                $search,
            ]), 0, 'integer'));
        }
        $sort = match ($sort) {
            'downloads', 'stars', 'plugins' => $sort,
            default => 'monthly_downloads',
        };

        return $query->orderBy([
            $sort => 'DESC',
            $this->aliasField('namespace') => 'ASC',
        ]);
    }

    /**
     * Batch profile owners and two popular plugins for the visible publishers.
     *
     * @param list<string> $namespaces Visible namespace names.
     * @param string $sort Popularity metric; plugin-count rankings preview monthly downloads.
     * @param string|null $major CakePHP major; null includes all versions.
     * @return array<string, array{githubUrl: string|null, plugins: list<array{id: int, package: string, name: string}>}>
     */
    public function publisherDetails(array $namespaces, string $sort = 'monthly_downloads', ?string $major = null): array
    {
        if ($namespaces === []) {
            return [];
        }
        $details = array_fill_keys($namespaces, ['githubUrl' => null, 'plugins' => []]);
        $owners = [];
        $metric = match ($sort) {
            'stars', 'downloads' => $sort,
            default => 'monthly_downloads',
        };
        $packages = $this->find()->select(['id', 'namespace', 'package', 'repo_url'])
            ->where(['namespace IN' => $namespaces])
            ->orderBy([
                $this->aliasField($metric) => 'DESC',
                $this->aliasField('package') => 'ASC',
            ]);
        if ($major !== null) {
            $packages->select(['publisher_compatible' => $packages->expr()->exists($this->publisherCompatibility($major))]);
        }
        foreach ($packages->all() as $package) {
            $namespace = $package->namespace;
            $owner = $this->githubOwner($package->repo_url);
            if ($owner !== null) {
                $owners[$namespace][$owner] = true;
            }
            if (($major === null || $package->get('publisher_compatible')) && count($details[$namespace]['plugins']) < 2) {
                $details[$namespace]['plugins'][] = [
                    'id' => $package->id,
                    'package' => $package->package,
                    'name' => explode('/', $package->package, 2)[1],
                ];
            }
        }
        foreach ($owners as $namespace => $namespaceOwners) {
            if (count($namespaceOwners) === 1) {
                $details[$namespace]['githubUrl'] = 'https://github.com/' . array_key_first($namespaceOwners);
            }
        }

        return $details;
    }

    /**
     * EXISTS counts a compatible package once even when several minor-version tags match.
     *
     * @return \Cake\ORM\Query\SelectQuery
     */
    private function publisherCompatibility(string $major): SelectQuery
    {
        $junction = $this->Tagged->getTarget();
        $alias = $junction->getAlias();

        return $junction->find()->select([$alias . '.fk_id'])
            ->innerJoin(['PublisherVersion' => $this->Tags->getTarget()->getTable()], ['PublisherVersion.id = ' . $alias . '.tag_id'])
            ->where([
                $alias . '.fk_model' => $this->getAlias(),
                'PublisherVersion.slug LIKE' => 'cakephp-' . $major . '-%',
            ])
            ->where(static fn(QueryExpression $exp) => $exp->equalFields($alias . '.fk_id', 'Packages.id'));
    }

    /**
     * Derive supported major choices from the catalogue's existing compatibility metadata.
     *
     * @return list<string>
     */
    public function publisherMajors(): array
    {
        $majors = [];
        foreach (array_keys($this->filterOptions()['cakephp']) as $slug) {
            if (preg_match('/^cakephp-(\d+)-\d+$/D', $slug, $matches)) {
                $major = $matches[1];
                if (!in_array($major, $majors, true)) {
                    $majors[] = $major;
                }
            }
        }

        return $majors;
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
     * Link only an unambiguous GitHub repository owner, never guess from the Composer prefix.
     */
    public function namespaceGithubUrl(string $namespace): ?string
    {
        $owners = [];
        $repositories = $this->find('namespace', namespace: $namespace)->select(['repo_url'])->distinct()->all();
        foreach ($repositories as $package) {
            $owner = $this->githubOwner($package->repo_url);
            if ($owner !== null) {
                $owners[$owner] = true;
            }
            if (count($owners) > 1) {
                return null;
            }
        }

        return $owners !== [] ? 'https://github.com/' . array_key_first($owners) : null;
    }

    /**
     * Resolve repository owners consistently for profile pages and publisher rows.
     */
    private function githubOwner(string $url): ?string
    {
        $url = preg_replace('#^git@github\.com:#i', 'https://github.com/', trim($url)) ?? $url;
        $host = strtolower((string)parse_url($url, PHP_URL_HOST));
        $parts = explode('/', trim((string)parse_url($url, PHP_URL_PATH), '/'));
        if (
            !in_array($host, ['github.com', 'www.github.com'], true) ||
            count($parts) < 2 || $parts[1] === '' ||
            !preg_match('/^[a-z0-9][a-z0-9-]*$/iD', $parts[0])
        ) {
            return null;
        }

        return strtolower($parts[0]);
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
