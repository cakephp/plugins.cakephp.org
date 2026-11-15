<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Cache\Cache;
use Cake\ORM\Query\SelectQuery;

/**
 * Public finder results share import invalidation and SQL-specific cache keys.
 */
trait CatalogueCacheTrait
{
    /**
     * Cache final query results and optional pagination totals, never island snapshots.
     *
     * @template TSubject of \Cake\Datasource\EntityInterface|array
     * @param \Cake\ORM\Query\SelectQuery<TSubject> $query Public read query.
     * @return \Cake\ORM\Query\SelectQuery<TSubject>
     */
    private function cachePublicQuery(SelectQuery $query, string $scope, bool $count = false): SelectQuery
    {
        $query->cache(static fn(SelectQuery $query): string => self::publicCacheKey($query, $scope), 'catalogue');
        if ($count) {
            $query->counter(static function (SelectQuery $query) use ($scope): int {
                return Cache::remember(self::publicCacheKey($query, $scope . '_count'), $query->count(...), 'catalogue');
            });
        }

        return $query;
    }

    /**
     * Resolve after pagination so SQL includes its limit, offset, order and bindings.
     *
     * @param \Cake\ORM\Query\SelectQuery $query Final public read query.
     */
    private static function publicCacheKey(SelectQuery $query, string $scope): string
    {
        return $scope . '_' . hash('sha256', serialize([
            $query->getConnection()->configName(),
            $query->sql(),
            $query->getValueBinder()->bindings(),
            $query->isHydrationEnabled(),
        ]));
    }
}
