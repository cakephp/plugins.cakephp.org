<?php
namespace App\Model\Table\Finder;

use Cake\ORM\Query;

trait Package13FinderTrait
{
    /**
     * Returns an 1.3 versioned Package
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function find13(Query $query, array $options)
    {
        return $query->find('versioned', ['version' => '1.3']);
    }
}
