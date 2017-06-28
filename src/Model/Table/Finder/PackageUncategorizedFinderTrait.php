<?php
namespace App\Model\Table\Finder;

use Cake\Log\Log;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\Query;
use InvalidArgumentException;

trait PackageUncategorizedFinderTrait
{
    /**
     * Returns an uncategorized Package
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findUncategorized(Query $query, array $options)
    {
        $query->where([
            "{$this->alias()}.category_id IS" => null,
        ]);

        return $query;
    }
}
