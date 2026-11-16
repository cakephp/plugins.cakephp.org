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
        $this->add('search', KeywordSearch::class, [
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
        $this->addTaggedSlugFilter('exclude_cakephp_slugs', exclude: true);
        $this->addTaggedSlugFilter('exclude_php_slugs', exclude: true);
    }

    /**
     * @param string $filterName
     * @return void
     */
    protected function addTaggedSlugFilter(string $filterName, bool $exclude = false): void
    {
        $this->callback($filterName, [
            'callback' => function (SelectQuery $query, array $args) use ($filterName, $exclude): bool {
                $value = $args[$filterName];
                if (is_array($value)) {
                    // Selection order and duplicates must not fragment the query cache.
                    $value = array_values(array_unique($value));
                    sort($value, SORT_STRING);
                }
                if ($exclude) {
                    $query->find('withoutTags', slugs: (array)$value);
                } else {
                    $query->find('tagged', value: $value);
                }

                return true;
            },
        ]);
    }
}
