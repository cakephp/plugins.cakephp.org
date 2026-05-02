<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Filter\PackagesCollection;
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
 * @mixin \Search\Model\Behavior\SearchBehavior
 * @mixin \Tags\Model\Behavior\TagBehavior
 * @extends \Cake\ORM\Table<array{Search: \Search\Model\Behavior\SearchBehavior, Tag: \Tags\Model\Behavior\TagBehavior}>
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
     * Finder for autocomplete search results.
     *
     * @param \Cake\ORM\Query\SelectQuery $query Query instance.
     * @param string $search Search term.
     * @param int $maxResults Maximum number of results.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findAutocomplete(SelectQuery $query, string $search, int $maxResults = 8): SelectQuery
    {
        $escapedSearch = str_replace(['%', '_'], ['\%', '\_'], $search);

        return $query
            ->find('search', search: ['search' => $search])
            ->contain(['Tags' => function (SelectQuery $q) {
                return $q->orderByDesc('Tags.label');
            }])
            ->selectAlso([
                'name_match' => $query->expr()
                    ->case()
                    ->when(['Packages.package LIKE' => '%' . $escapedSearch . '%'])
                    ->then(1, 'integer')
                    ->else(0, 'integer'),
            ])
            ->orderByDesc('name_match')
            ->orderByDesc('Packages.downloads')
            ->limit($maxResults);
    }
}
