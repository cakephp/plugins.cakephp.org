<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Text;
use Cake\Validation\Validator;

/**
 * Tags Model
 *
 * @property \Cake\ORM\Association\HasMany $Tagged
 *
 * @method \App\Model\Entity\Tag get($primaryKey, $options = [])
 * @method \App\Model\Entity\Tag newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Tag[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Tag|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Tag patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Tag[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Tag findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TagsTable extends Table
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

        $this->setTable('tags');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Tagged', [
            'foreignKey' => 'tag_id'
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
            ->allowEmpty('identifier');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->requirePresence('keyname', 'create')
            ->notEmpty('keyname');

        $validator
            ->integer('occurrence')
            ->requirePresence('occurrence', 'create')
            ->notEmpty('occurrence');

        return $validator;
    }

    public function add($tagStr)
    {
        $identifier = 'has';
        $name = $tagStr;
        $parts = explode(':', $tagStr);
        if (count($parts) == 2) {
            $identifier = $parts[0];
            $name = $parts[1];
        }

        $keyname = $this->multibyteKey($name);
        $data = [
            'identifier' => $identifier,
            'keyname' => $keyname,
        ];
        return $this->findOrCreate($data, function ($entity) use ($name) {
            $entity->id = Text::uuid();
            $entity->name = $name;
            return $entity;
        });
    }

    /**
     * Creates a multibyte safe unique key
     *
     * @param Model $model Model instance that behavior is attached to
     * @param string $string Tag name string
     * @return string Multibyte safe key string
     */
    public function multibyteKey($string = null)
    {
        $str = mb_strtolower($string);
        $str = preg_replace('/\xE3\x80\x80/', ' ', $str);
        $str = str_replace(array('_', '-'), '', $str);
        $str = preg_replace('#[:\#\*"()~$^{}`@+=;,<>!&%\.\]\/\'\\\\|\[]#', "\x20", $str);
        $str = str_replace('?', '', $str);
        $str = trim($str);
        $str = preg_replace('#\x20+#', '', $str);
        return $str;
    }
}
