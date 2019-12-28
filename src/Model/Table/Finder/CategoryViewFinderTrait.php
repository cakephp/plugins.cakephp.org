<?php
namespace App\Model\Table\Finder;

use Cake\ORM\Query;
use InvalidArgumentException;

trait CategoryViewFinderTrait
{
    /**
     * Returns a query scoped by Maintainers.id
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findView(Query $query, array $options)
    {
        if (empty($options['slug'])) {
            throw new InvalidArgumentException(__('Missing slug argument'));
        }
        $slug = $options['slug'];

        $query->limit(1);
        $query->where([
            "{$this->getAlias()}.slug" => $slug
        ]);

        return $query;
    }
}
