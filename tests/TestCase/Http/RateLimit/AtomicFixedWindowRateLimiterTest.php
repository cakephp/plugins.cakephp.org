<?php
declare(strict_types=1);

namespace App\Test\TestCase\Http\RateLimit;

use App\Http\RateLimit\AtomicFixedWindowRateLimiter;
use Cake\Cache\Engine\ArrayEngine;
use Cake\TestSuite\TestCase;
use DebugKit\Cache\Engine\DebugEngine;
use Psr\Log\NullLogger;

class AtomicFixedWindowRateLimiterTest extends TestCase
{
    /**
     * @return void
     */
    public function testItRejectsRequestsPastTheLimit(): void
    {
        $cache = new ArrayEngine();
        $cache->setConfig('prefix', '');
        $limiter = new AtomicFixedWindowRateLimiter($cache);

        $this->assertTrue($limiter->attempt('visitor', 2, 60)['allowed']);
        $this->assertTrue($limiter->attempt('visitor', 2, 60)['allowed']);

        $result = $limiter->attempt('visitor', 2, 60);
        $this->assertFalse($result['allowed']);
        $this->assertSame(0, $result['remaining']);
    }

    /**
     * DebugKit must not replace Redis's atomic add with the wrapper's fallback.
     */
    public function testDebugKitWrapperUsesUnderlyingEngine(): void
    {
        $cache = new ArrayEngine();
        $cache->init(['prefix' => 'limiter_test_']);
        $debug = new DebugEngine($cache, 'rate_limit', new NullLogger());
        $debug->init();
        $limiter = new AtomicFixedWindowRateLimiter($debug);

        $this->assertTrue($limiter->attempt('visitor', 1, 60)['allowed']);
        $this->assertFalse($limiter->attempt('visitor', 1, 60)['allowed']);
    }
}
