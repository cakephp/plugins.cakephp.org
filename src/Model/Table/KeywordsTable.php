<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Keyword;
use App\Utility\ComposerKeyword;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @extends \Cake\ORM\Table<array{}, \App\Model\Entity\Keyword>
 * @property \Cake\ORM\Association\BelongsToMany<\App\Model\Table\PackagesTable> $Packages
 */
class KeywordsTable extends Table
{
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
     * @param \Cake\Validation\Validator $validator Validator instance.
     */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator->scalar('name')->requirePresence('name', 'create')->notEmptyString('name')
            ->maxLength('name', ComposerKeyword::MAX_LENGTH);
    }

    /**
     * @param \Cake\ORM\RulesChecker $rules Rules checker.
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['name']));

        return $rules;
    }

    /**
     * Reuse shared terms; new terms are saved atomically with their package.
     *
     * @param list<string> $names Normalized keyword names.
     * @return list<\App\Model\Entity\Keyword>
     */
    public function forNames(array $names): array
    {
        if ($names === []) {
            return [];
        }
        $existing = $this->find()->where(['name IN' => $names])->all()->indexBy('name')->toArray();

        return array_map(fn(string $name): Keyword => $existing[$name] ?? $this->newEntity(['name' => $name]), $names);
    }
}
