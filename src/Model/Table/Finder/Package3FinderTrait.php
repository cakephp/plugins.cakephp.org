<?php
namespace App\Model\Table\Finder;

use Cake\ORM\Query;

trait Package3FinderTrait
{
    /**
     * Returns an 3.x versioned Package
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function find3(Query $query, array $options)
    {
        return $query->find('versioned', ['version' => '3']);
    }
}
