<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Editorial configuration stays independent of imported catalogue records.
 *
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}>
 * @method \App\Model\Entity\FeaturedPlugin get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\FeaturedPlugin newEmptyEntity()
 * @method \App\Model\Entity\FeaturedPlugin newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\FeaturedPlugin patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Cake\ORM\Query\SelectQuery<\App\Model\Entity\FeaturedPlugin> find(string $type = 'all', mixed ...$args)
 */
class FeaturedPluginsTable extends Table
{
    /**
     * Match by canonical name even if catalogue IDs change after a clean sync.
     *
     * @param array<string, mixed> $config Table configuration.
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setDisplayField('package');
        $this->addBehavior('Timestamp');
        $this->belongsTo('CataloguePackages', ['className' => 'Packages', 'foreignKey' => 'package', 'bindingKey' => 'package', 'propertyName' => 'catalogue_package']);
    }

    /**
     * Logos are browser-loaded images; never fetch or interpret editorial HTML.
     */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->scalar('package')->maxLength('package', 191)->requirePresence('package', 'create')->notEmptyString('package')
            ->boolean('enabled')->notEmptyString('enabled')
            ->scalar('title')->maxLength('title', 255)->allowEmptyString('title')
            ->scalar('description')->maxLength('description', 2000)->allowEmptyString('description')
            ->scalar('logo')->maxLength('logo', 2048)->allowEmptyString('logo')
            ->add('logo', 'imageLocation', [
                'rule' => static fn(string $value): bool => (str_starts_with($value, 'https://') && filter_var($value, FILTER_VALIDATE_URL) !== false)
                    || (bool)preg_match('#^/img/[a-zA-Z0-9_./-]+$#D', $value) && !str_contains($value, '..'),
                'message' => 'Use an HTTPS URL or a local /img/ path.',
            ]);
    }

    /**
     * The unique index also protects concurrent additions.
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['package']), ['errorField' => 'package']);

        return $rules;
    }
}
