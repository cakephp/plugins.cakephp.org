<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Search\Manager;

/**
 * Packages Model
 *
 * @method \App\Model\Entity\Package newEmptyEntity()
 * @method \App\Model\Entity\Package newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Package> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Package get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Package findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Package patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Package> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Package|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Package saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Package>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Package>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Package>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Package> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Package>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Package>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Package>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Package> deleteManyOrFail(iterable $entities, array $options = [])
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

        $this->addBehavior('Search.Search');
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
            ->scalar('packagist_url')
            ->maxLength('packagist_url', 255)
            ->requirePresence('packagist_url', 'create')
            ->notEmptyString('packagist_url');

        $validator
            ->integer('downloads')
            ->requirePresence('downloads', 'create')
            ->notEmptyString('downloads');

        $validator
            ->integer('stars')
            ->requirePresence('stars', 'create')
            ->notEmptyString('stars');

        return $validator;
    }

    /**
     * @return \Search\Manager
     */
    public function searchManager(): Manager
    {
        /** @var \Search\Model\Behavior\SearchBehavior $search */
        $search = $this->getBehavior('Search');
        $searchManager = $search->searchManager();
        $searchManager->add('search', 'Search.Like', [
            'before' => true,
            'after' => true,
            'fieldMode' => 'OR',
            'comparison' => 'LIKE',
            'wildcardAny' => '*',
            'wildcardOne' => '?',
            'fields' => ['package', 'description'],
        ]);

        return $searchManager;
    }
}
