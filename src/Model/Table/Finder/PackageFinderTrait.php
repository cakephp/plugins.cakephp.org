<?php
namespace App\Model\Table\Finder;

use Cake\Log\Log;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\Query;
use InvalidArgumentException;

trait PackageFinderTrait
{
    /**
     * Returns a valid Package with all related data
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findPackage(Query $query, array $options)
    {
        $query->contain([
            'Categories',
            'Maintainers',
        ]);
        $query->where([
            "{$this->getAlias()}.deleted" => false,
        ]);

        return $query;
    }
}
