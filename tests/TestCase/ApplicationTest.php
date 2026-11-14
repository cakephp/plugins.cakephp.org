<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Test\TestCase;

use App\Application;
use Cake\Cache\Cache;
use Cake\Cache\Engine\ArrayEngine;
use Cake\Core\Configure;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\Exception\TooManyRequestsException;
use Cake\Http\Middleware\RateLimitMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * ApplicationTest class
 */
class ApplicationTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $rateLimitCacheConfig = null;

    /**
     * Isolate middleware counters from development and production Redis settings.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->rateLimitCacheConfig = Cache::getConfig('rate_limit');
        Cache::drop('rate_limit');
        Cache::setConfig('rate_limit', ['className' => ArrayEngine::class]);
        Configure::write('PublicRequestProtection.rateLimitEnabled', false);
    }

    /**
     * Restore the application's rate-limit cache after each test.
     */
    protected function tearDown(): void
    {
        Cache::drop('rate_limit');
        if ($this->rateLimitCacheConfig !== null) {
            Cache::setConfig('rate_limit', $this->rateLimitCacheConfig);
        }
        parent::tearDown();
    }

    /**
     * Test bootstrap in production.
     *
     * @return void
     */
    public function testBootstrap(): void
    {
        Configure::write('debug', false);
        $app = new Application(dirname(__DIR__, 2) . '/config');
        $app->bootstrap();
        $plugins = $app->getPlugins();

        $this->assertTrue($plugins->has('Bake'), 'plugins has Bake?');
        $this->assertFalse($plugins->has('DebugKit'), 'plugins has DebugKit?');
        $this->assertTrue($plugins->has('Migrations'), 'plugins has Migrations?');
    }

    /**
     * Test bootstrap add DebugKit plugin in debug mode.
     *
     * @return void
     */
    public function testBootstrapInDebug(): void
    {
        Configure::write('debug', true);
        $app = new Application(dirname(__DIR__, 2) . '/config');
        $app->bootstrap();
        $plugins = $app->getPlugins();

        $this->assertTrue($plugins->has('DebugKit'), 'plugins has DebugKit?');
    }

    /**
     * Development retains the plugin asset fallback.
     *
     * @return void
     */
    public function testMiddleware(): void
    {
        Configure::write('debug', true);
        $app = new Application(dirname(__DIR__, 2) . '/config');
        $middleware = new MiddlewareQueue();

        $middleware = $app->middleware($middleware);

        $this->assertInstanceOf(ErrorHandlerMiddleware::class, $middleware->current());
        $middleware->seek(1);
        $this->assertInstanceOf(AssetMiddleware::class, $middleware->current());
        $middleware->seek(2);
        $this->assertInstanceOf(RoutingMiddleware::class, $middleware->current());
    }

    /**
     * Production relies on deployed asset symlinks instead of serving them through PHP.
     */
    public function testProductionMiddlewareSkipsAssetMiddleware(): void
    {
        Configure::write('debug', false);
        $middleware = new Application(dirname(__DIR__, 2) . '/config')->middleware(new MiddlewareQueue());
        $middleware->seek(1);
        $this->assertInstanceOf(RoutingMiddleware::class, $middleware->current());
        foreach ($middleware as $layer) {
            $this->assertNotInstanceOf(AssetMiddleware::class, $layer);
        }
    }

    /**
     * Only POST updates consume quota; normal pages and admin paths are skipped.
     */
    public function testOnlyInteractUpdatesConsumeQuota(): void
    {
        Configure::write('debug', false);
        $limiter = $this->rateLimiter();
        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new Response());
        $requests = [
            ['/', 'GET', []],
            ['/', 'GET', ['search' => 'users']],
            ['/publishers', 'GET', []],
            ['/publishers', 'GET', ['sort' => 'stars']],
            ['/plugins/markstory/asset_compress', 'GET', []],
            ['/plugins/vendor.name/plugin_name.json', 'GET', []],
            ['/plugins/dereuromark', 'GET', []],
            ['/plugins/dereuromark', 'GET', ['search' => 'tools']],
            ['/', 'HEAD', []],
            ['/requirements', 'GET', []],
            ['/auth/login', 'POST', []],
            ['/admin', 'GET', []],
            ['/admin/users', 'GET', []],
            ['/admin/users', 'POST', []],
            ['/admin/_interact/update', 'POST', []],
            ['/_interact/update', 'GET', []],
            ['/_interact/update', 'HEAD', []],
        ];
        foreach ($requests as [$path, $method, $query]) {
            $request = new ServerRequest([
                'url' => $path,
                'environment' => [
                    'REQUEST_METHOD' => $method,
                    'REMOTE_ADDR' => '203.0.113.1',
                ],
            ])->withQueryParams($query);
            $response = $limiter->process($request, $handler);
            $this->assertFalse($response->hasHeader('X-RateLimit-Limit'), $method . ' ' . $path);
        }
        $request = new ServerRequest([
            'url' => '/_interact/update',
            'environment' => [
                'REQUEST_METHOD' => 'POST',
                'REMOTE_ADDR' => '203.0.113.1',
            ],
        ]);
        $response = $limiter->process($request, $handler);
        $this->assertSame('6000', $response->getHeaderLine('X-RateLimit-Limit'));
        $this->assertSame('5999', $response->getHeaderLine('X-RateLimit-Remaining'));
    }

    /**
     * The live-update transport cannot bypass the atomic counter.
     */
    public function testInteractUpdatesAreThrottled(): void
    {
        Configure::write('PublicRequestProtection.limit', 2);
        $limiter = $this->rateLimiter();
        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new Response());
        $request = new ServerRequest([
            'url' => '/_interact/update',
            'environment' => [
                'REQUEST_METHOD' => 'POST',
                'REMOTE_ADDR' => '203.0.113.2',
            ],
        ]);
        $limiter->process($request, $handler);
        $limiter->process($request, $handler);
        $this->expectException(TooManyRequestsException::class);
        $limiter->process($request, $handler);
    }

    /**
     * Spoofed forwarding headers cannot change the connecting IP's quota.
     */
    public function testForwardedHeadersCannotChangeQuota(): void
    {
        Configure::write('PublicRequestProtection.limit', 1);
        $limiter = $this->rateLimiter();
        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new Response());
        $request = new ServerRequest([
            'url' => '/_interact/update',
            'environment' => [
                'REQUEST_METHOD' => 'POST',
                'REMOTE_ADDR' => '203.0.113.3',
            ],
        ])->withHeader('X-Forwarded-For', '198.51.100.1')->withHeader('CF-Connecting-IP', '198.51.100.1');
        $this->assertSame('0', $limiter->process($request, $handler)->getHeaderLine('X-RateLimit-Remaining'));
        $this->assertSame('0', $limiter->process($request->withEnv('REMOTE_ADDR', '203.0.113.4'), $handler)->getHeaderLine('X-RateLimit-Remaining'));
        try {
            $limiter->process($request->withHeader('X-Forwarded-For', '198.51.100.2')->withHeader('CF-Connecting-IP', '198.51.100.2'), $handler);
            $this->fail('Forwarded headers must not reset the counter.');
        } catch (TooManyRequestsException $exception) {
            $this->assertArrayHasKey('Retry-After', $exception->getHeaders());
        }
    }

    /**
     * Resolve the enabled limiter from the actual application middleware queue.
     */
    protected function rateLimiter(): RateLimitMiddleware
    {
        Configure::write('PublicRequestProtection.rateLimitEnabled', true);
        $middleware = new Application(dirname(__DIR__, 2) . '/config')->middleware(new MiddlewareQueue());
        foreach ($middleware as $limiter) {
            if ($limiter instanceof RateLimitMiddleware) {
                return $limiter;
            }
        }

        $this->fail('Rate limiting middleware was not registered.');
    }
}
