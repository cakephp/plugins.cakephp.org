<?php
namespace App\Model\Table\Finder;

use Cake\ORM\Query;

trait PackageFeaturedFinderTrait
{
    /**
     * Returns an featured Package
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findFeatured(Query $query, array $options)
    {
        $query->where([
            "{$this->alias()}.deleted" => false,
            "{$this->alias()}.featured" => true,
        ]);

        return $query;
    }
}
