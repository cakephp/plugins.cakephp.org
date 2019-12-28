<?php
namespace App\Model\Table\Finder;

use Cake\ORM\Query;

trait PackageDeletedFinderTrait
{
    /**
     * Returns a deleted Package
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDeleted(Query $query, array $options)
    {
        $query->where([
            "{$this->getAlias()}.deleted" => true,
        ]);

        return $query;
    }
}
