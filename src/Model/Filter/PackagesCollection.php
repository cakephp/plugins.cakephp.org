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
        ])
            ->callback('cakephp_slugs', [
                'callback' => function (SelectQuery $query, array $args, $manager): void {
                    // Here you would have to remap $args if key isn't the expected "tag"
                    $args['slug'] = $args['cakephp_slugs'];
                    unset($args['cakephp_slugs']);
                    $query->find('tagged', ...$args);
                },
            ])
            ->callback('php_slugs', [
                'callback' => function (SelectQuery $query, array $args, $manager): void {
                    // Here you would have to remap $args if key isn't the expected "tag"
                    $args['slug'] = $args['php_slugs'];
                    unset($args['php_slugs']);
                    $query->find('tagged', ...$args);
                },
            ]);
    }
}
