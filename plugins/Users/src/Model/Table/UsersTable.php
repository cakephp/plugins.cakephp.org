<?php
namespace Users\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Users\Model\Table\Traits\AccountValidationTrait;
use Users\Model\Table\Traits\SocialAuthTrait;
use Users\Model\Table\Traits\TokenTrait;

/**
 * Users Model
 *
 * @method \Users\Model\Entity\User get($primaryKey, $options = [])
 * @method \Users\Model\Entity\User newEntity($data = null, array $options = [])
 * @method \Users\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \Users\Model\Entity\User|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Users\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Users\Model\Entity\User[] patchEntities($entities, array $data, array $options = [])
 * @method \Users\Model\Entity\User findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
{
    use AccountValidationTrait;
    use SocialAuthTrait;
    use TokenTrait;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('users');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        if (Configure::read('Users.enablePasswordReset') === true) {
            $this->addBehavior('Muffin/Tokenize.Tokenize');
        }

        if (Configure::read('Users.enableAvatarUploads') === true) {
            $this->addBehavior('Josegonzalez/Upload.Upload', [
                'avatar' => [
                    'fields' => [
                        'dir' => 'avatar_dir',
                    ],
                ],
            ]);
        }
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
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email');

        $validator
            ->requirePresence('password', 'create')
            ->notEmpty('password');

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
        $rules->add($rules->isUnique(['email']));

        return $rules;
    }
}
