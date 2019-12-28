<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Packages Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Maintainers
 * @property \Cake\ORM\Association\BelongsTo $Categories
 *
 * @method \App\Model\Entity\Package get($primaryKey, $options = [])
 * @method \App\Model\Entity\Package newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Package[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Package|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Package patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Package[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Package findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class PackagesTable extends Table
{

    use \App\Model\Table\Finder\PackageDeletedFinderTrait;
    use \App\Model\Table\Finder\PackageFeaturedFinderTrait;
    use \App\Model\Table\Finder\PackageFinderTrait;
    use \App\Model\Table\Finder\PackageUncategorizedFinderTrait;
    use \App\Model\Table\Finder\PackageUnversionedFinderTrait;
    use \App\Model\Table\Finder\PackageIndexFinderTrait;
    use \App\Model\Table\Finder\PackageViewFinderTrait;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('packages');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->addBehavior('Search.Search');
        $this->searchManager()
            ->useCollection('admin')
            ->add('q', 'Search.Like', [
                'after' => true,
                'before' => true,
                'field' => ['name'],
                'form' => [
                    'label' => 'Package name',
                ]
            ]);

        $this->belongsTo('Maintainers', [
            'foreignKey' => 'maintainer_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->allowEmpty('repository_url');

        $validator
            ->allowEmpty('bakery_article');

        $validator
            ->allowEmpty('homepage');

        $validator
            ->allowEmpty('description');

        $validator
            ->allowEmpty('tags');

        $validator
            ->integer('open_issues')
            ->requirePresence('open_issues', 'create')
            ->notEmpty('open_issues');

        $validator
            ->integer('forks')
            ->requirePresence('forks', 'create')
            ->notEmpty('forks');

        $validator
            ->integer('watchers')
            ->requirePresence('watchers', 'create')
            ->notEmpty('watchers');

        $validator
            ->integer('collaborators')
            ->requirePresence('collaborators', 'create')
            ->notEmpty('collaborators');

        $validator
            ->integer('contributors')
            ->requirePresence('contributors', 'create')
            ->notEmpty('contributors');

        $validator
            ->dateTime('created_at')
            ->allowEmpty('created_at');

        $validator
            ->dateTime('last_pushed_at')
            ->allowEmpty('last_pushed_at');

        $validator
            ->boolean('deleted')
            ->requirePresence('deleted', 'create')
            ->notEmpty('deleted');

        $validator
            ->integer('forks_count')
            ->requirePresence('forks_count', 'create')
            ->notEmpty('forks_count');

        $validator
            ->integer('network_count')
            ->requirePresence('network_count', 'create')
            ->notEmpty('network_count');

        $validator
            ->integer('open_issues_count')
            ->requirePresence('open_issues_count', 'create')
            ->notEmpty('open_issues_count');

        $validator
            ->integer('stargazers_count')
            ->requirePresence('stargazers_count', 'create')
            ->notEmpty('stargazers_count');

        $validator
            ->integer('subscribers_count')
            ->requirePresence('subscribers_count', 'create')
            ->notEmpty('subscribers_count');

        $validator
            ->integer('watchers_count')
            ->requirePresence('watchers_count', 'create')
            ->notEmpty('watchers_count');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['maintainer_id'], 'Maintainers'));
        $rules->add($rules->existsIn(['category_id'], 'Categories'));

        return $rules;
    }
}
