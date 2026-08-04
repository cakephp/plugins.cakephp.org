<?php
declare(strict_types=1);

namespace App\Http\RateLimit;

use Cake\Cache\CacheEngineInterface;
use Cake\Http\RateLimit\RateLimiterInterface;
use LogicException;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;

/**
 * A fixed-window limiter backed by an atomic cache increment operation.
 *
 * Redis is required in production: the file cache does not provide atomic
 * increments and would allow concurrent requests to bypass the limit.
 */
class AtomicFixedWindowRateLimiter implements RateLimiterInterface
{
    /**
     * @var \Psr\SimpleCache\CacheInterface&\Cake\Cache\CacheEngineInterface
     */
    private CacheInterface&CacheEngineInterface $cache;

    /**
     * @param \Psr\SimpleCache\CacheInterface&\Cake\Cache\CacheEngineInterface $cache Cache backend.
     */
    public function __construct(CacheInterface $cache)
    {
        if (!$cache instanceof CacheEngineInterface) {
            throw new RuntimeException('Rate limiting requires a CakePHP cache engine.');
        }
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function attempt(string $identifier, int $limit, int $window, int $cost = 1): array
    {
        $now = time();
        $windowStart = intdiv($now, $window) * $window;
        $key = $identifier . '_' . $windowStart;

        if ($this->cache->add($key, $cost)) {
            $count = $cost;
        } else {
            try {
                $count = $this->cache->increment($key, $cost);
            } catch (LogicException $exception) {
                throw new RuntimeException('Rate limiting requires a cache backend with atomic increments.', 0, $exception);
            }
        }

        if ($count === false) {
            throw new RuntimeException('Unable to update rate-limit counter.');
        }

        return [
            'allowed' => $count <= $limit,
            'limit' => $limit,
            'remaining' => max(0, $limit - $count),
            'reset' => $windowStart + $window,
        ];
    }

    /**
     * @inheritDoc
     */
    public function reset(string $identifier): void
    {
        $this->cache->delete($identifier);
    }
}
