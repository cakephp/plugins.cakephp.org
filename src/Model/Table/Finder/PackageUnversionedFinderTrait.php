<?php
namespace App\Model\Table\Finder;

use Cake\ORM\Query;

trait PackageUnversionedFinderTrait
{
    /**
     * Returns an unversioned Package
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findUnversioned(Query $query, array $options)
    {
        $query->where([
            "{$this->getAlias()}.deleted" => false,
            'or' => [
                "{$this->getAlias()}.tags NOT LIKE" => '%version:%',
                "{$this->getAlias()}.tags IS" => null,
            ]
        ]);

        return $query;
    }
}
