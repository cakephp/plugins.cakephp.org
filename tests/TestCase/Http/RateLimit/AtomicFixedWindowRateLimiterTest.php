<?php
declare(strict_types=1);

namespace App\Test\TestCase\Http\RateLimit;

use App\Http\RateLimit\AtomicFixedWindowRateLimiter;
use Cake\Cache\Engine\ArrayEngine;
use Cake\TestSuite\TestCase;

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
}
