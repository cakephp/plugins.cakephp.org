<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Enum\FeaturedPluginStatus;
use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ORM\Query\SelectQuery;
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
        $this->belongsTo('CataloguePackages', [
            'className' => PackagesTable::class,
            'foreignKey' => 'package',
            'bindingKey' => 'package',
            'propertyName' => 'catalogue_package',
        ]);
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
                'rule' => static fn(mixed $value): bool => is_string($value) && (
                    (str_starts_with($value, 'https://') && filter_var($value, FILTER_VALIDATE_URL) !== false)
                    || (bool)preg_match('#^/img/[a-zA-Z0-9_./-]+$#D', $value) && !str_contains($value, '..')
                ),
                'message' => 'Use an HTTPS URL or a local /img/ path.',
            ]);
    }

    /**
     * Validate the same trimmed override values that entity setters persist.
     *
     * @param \Cake\Event\EventInterface $event Marshal event.
     * @param \ArrayObject<string, mixed> $data Input fields.
     * @param \ArrayObject<string, mixed> $options Marshal options.
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        foreach (['logo', 'title', 'description'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }
    }

    /**
     * The unique index also protects concurrent additions.
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['package']), ['errorField' => 'package']);
        $rules->addCreate($rules->existsIn(['package'], 'CataloguePackages'), 'cataloguePackage', [
            'errorField' => 'package',
            'message' => 'Choose an existing plugin.',
        ]);

        return $rules;
    }

    /**
     * Build the management listing with catalogue counts and availability.
     *
     * @param \Cake\ORM\Query\SelectQuery<\App\Model\Entity\FeaturedPlugin> $query Featured entry query.
     * @return \Cake\ORM\Query\SelectQuery<\App\Model\Entity\FeaturedPlugin>
     */
    public function findAdminListing(SelectQuery $query, string $search = '', ?FeaturedPluginStatus $status = null): SelectQuery
    {
        $query->contain(['CataloguePackages']);
        if (trim($search) !== '') {
            $query->where(['FeaturedPlugins.package LIKE' => '%' . trim($search) . '%']);
        }
        if ($status === FeaturedPluginStatus::Unavailable) {
            $query->where(['CataloguePackages.id IS' => null]);
        } elseif ($status instanceof FeaturedPluginStatus) {
            $query->where(['FeaturedPlugins.enabled' => $status === FeaturedPluginStatus::Enabled]);
        }

        return $query;
    }
}
