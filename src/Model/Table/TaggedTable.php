<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Text;
use Cake\Validation\Validator;

/**
 * Tagged Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Tags
 *
 * @method \App\Model\Entity\Tagged get($primaryKey, $options = [])
 * @method \App\Model\Entity\Tagged newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Tagged[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Tagged|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Tagged patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Tagged[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Tagged findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TaggedTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('tagged');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Tags', [
            'foreignKey' => 'tag_id',
            'joinType' => 'INNER',
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
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('foreign_key', 'create')
            ->notEmpty('foreign_key');

        $validator
            ->requirePresence('model', 'create')
            ->notEmpty('model');

        $validator
            ->allowEmpty('language');

        $validator
            ->integer('times_tagged')
            ->requirePresence('times_tagged', 'create')
            ->notEmpty('times_tagged');

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
        $rules->add($rules->existsIn(['tag_id'], 'Tags'));

        return $rules;
    }

    public function addTagToPackage($tag, $package)
    {
        $data = [
            'foreign_key' => $package->id,
            'tag_id' => $tag->id,
            'model' => 'Package',
            'language' => 'en-us',
        ];

        return $this->findOrCreate($data, function ($entity) {
            $entity->id = Text::uuid();
            $entity->times_tagged = 1;

            return $entity;
        });
    }
}
