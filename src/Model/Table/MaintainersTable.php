<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Maintainers Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Gravatars
 * @property \Cake\ORM\Association\BelongsTo $Githubs
 * @property \Cake\ORM\Association\HasMany $Packages
 *
 * @method \App\Model\Entity\Maintainer get($primaryKey, $options = [])
 * @method \App\Model\Entity\Maintainer newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Maintainer[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Maintainer|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Maintainer patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Maintainer[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Maintainer findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MaintainersTable extends Table
{
    use \App\Model\Table\Finder\MaintainerViewFinderTrait;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('maintainers');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Packages', [
            'foreignKey' => 'maintainer_id',
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
            ->requirePresence('group', 'create')
            ->notEmpty('group');

        $validator
            ->requirePresence('username', 'create')
            ->notEmpty('username')
            ->add('username', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->email('email')
            ->allowEmpty('email');

        $validator
            ->allowEmpty('name');

        $validator
            ->allowEmpty('alias');

        $validator
            ->allowEmpty('url');

        $validator
            ->allowEmpty('twitter_username');

        $validator
            ->allowEmpty('company');

        $validator
            ->allowEmpty('location');

        $validator
            ->allowEmpty('password');

        $validator
            ->requirePresence('activation_key', 'create')
            ->notEmpty('activation_key');

        $validator
            ->requirePresence('avatar_url', 'create')
            ->notEmpty('avatar_url');

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
        $rules->add($rules->isUnique(['username']));
        $rules->add($rules->isUnique(['email']));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['gravatar_id'], 'Gravatars'));
        $rules->add($rules->existsIn(['github_id'], 'Githubs'));

        return $rules;
    }
}
