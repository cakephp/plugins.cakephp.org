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
     * @return void
     */
    protected function addTaggedSlugFilter(string $filterName): void
    {
        $this->callback($filterName, [
            'callback' => function (SelectQuery $query, array $args) use ($filterName): void {
                $args['slug'] = $args[$filterName];
                unset($args[$filterName]);
                $query->find('tagged', ...$args);
            },
        ]);
    }
}
