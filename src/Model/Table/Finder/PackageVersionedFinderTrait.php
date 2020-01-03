<?php
namespace App\Model\Table\Finder;

use Cake\ORM\Query;
use InvalidArgumentException;

trait PackageVersionedFinderTrait
{
    /**
     * Returns an versioned Package
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findVersioned(Query $query, array $options)
    {
        if (empty($options['version'])) {
            throw new InvalidArgumentException(__('Missing version argument'));
        }

        $query->where([
            "{$this->getAlias()}.deleted" => false,
            "{$this->getAlias()}.tags LIKE" => '%version:' . $options['version'] . '%',
        ]);

        return $query;
    }
}
