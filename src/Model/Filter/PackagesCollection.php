<?php
declare(strict_types=1);

namespace App\Model\Filter;

use Cake\ORM\Query\SelectQuery;
use Search\Model\Filter\FilterCollection;

class PackagesCollection extends FilterCollection
{
    /**
     * @return void
     */
    public function initialize(): void
    {
        $this->add('search', 'Search.Like', [
            'before' => true,
            'after' => true,
            'fieldMode' => 'OR',
            'comparison' => 'LIKE',
            'wildcardAny' => '*',
            'wildcardOne' => '?',
            'fields' => ['package', 'description'],
        ]);

        $this->addTaggedSlugFilter('cakephp_slugs');
        $this->addTaggedSlugFilter('php_slugs');
    }

    /**
     * @param string $filterName
     * @return void
     */
    protected function addTaggedSlugFilter(string $filterName): void
    {
        $this->callback($filterName, [
            'callback' => function (SelectQuery $query, array $args) use ($filterName): bool {
                $value = $args[$filterName];
                if (is_array($value)) {
                    // Selection order and duplicates must not fragment the query cache.
                    $value = array_values(array_unique($value));
                    sort($value, SORT_STRING);
                }
                $query->find('tagged', value: $value);

                return true;
            },
        ]);
    }
}
