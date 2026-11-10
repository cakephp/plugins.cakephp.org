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
     * testMiddleware
     *
     * @return void
     */
    public function testMiddleware(): void
    {
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
     * Public GET requests and Interact POST updates receive their configured limits.
     */
    public function testRateLimitProfiles(): void
    {
        $limiter = $this->rateLimiter();
        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new Response());
        $requests = [
            ['/', 'GET', [], 90],
            ['/', 'GET', ['search' => 'users'], 15],
            ['/plugins/1', 'GET', [], 90],
            ['/_interact/update', 'POST', [], 30],
        ];
        foreach ($requests as [$path, $method, $query, $limit]) {
            $request = new ServerRequest([
                'url' => $path,
                'environment' => ['REQUEST_METHOD' => $method, 'REMOTE_ADDR' => '203.0.113.1'],
            ])->withQueryParams($query);
            $response = $limiter->process($request, $handler);
            $this->assertSame((string)$limit, $response->getHeaderLine('X-RateLimit-Limit'));
        }
        $request = new ServerRequest(['url' => '/requirements']);
        $this->assertFalse($limiter->process($request, $handler)->hasHeader('X-RateLimit-Limit'));
        $request = new ServerRequest([
            'url' => '/auth/login', 'environment' => ['REQUEST_METHOD' => 'POST'],
        ]);
        $this->assertFalse($limiter->process($request, $handler)->hasHeader('X-RateLimit-Limit'));
    }

    /**
     * The live-update transport cannot bypass the atomic counter.
     */
    public function testInteractUpdatesAreThrottled(): void
    {
        Configure::write('PublicRequestProtection.rateLimits.interact', ['limit' => 2, 'window' => 60]);
        $limiter = $this->rateLimiter();
        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new Response());
        $request = new ServerRequest([
            'url' => '/_interact/update',
            'environment' => ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '203.0.113.2'],
        ]);
        $limiter->process($request, $handler);
        $limiter->process($request, $handler);
        $this->expectException(TooManyRequestsException::class);
        $limiter->process($request, $handler);
    }

    /**
     * Forwarded headers identify visitors only when sent by a configured proxy.
     */
    public function testForwardedAddressesRequireTrustedProxy(): void
    {
        Configure::write('PublicRequestProtection.trustedProxyIps', ['127.0.0.1']);
        Configure::write('PublicRequestProtection.rateLimits.browse', ['limit' => 1, 'window' => 60]);
        $limiter = $this->rateLimiter();
        $handler = $this->createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(new Response());
        $trusted = new ServerRequest([
            'url' => '/', 'environment' => ['REMOTE_ADDR' => '127.0.0.1'],
        ]);
        $this->assertSame('0', $limiter->process($trusted->withHeader('X-Forwarded-For', '198.51.100.1'), $handler)->getHeaderLine('X-RateLimit-Remaining'));
        $this->assertSame('0', $limiter->process($trusted->withHeader('X-Forwarded-For', '198.51.100.2'), $handler)->getHeaderLine('X-RateLimit-Remaining'));

        $untrusted = $trusted->withEnv('REMOTE_ADDR', '203.0.113.3');
        $limiter->process($untrusted->withHeader('X-Forwarded-For', '198.51.100.3'), $handler);
        $this->expectException(TooManyRequestsException::class);
        $limiter->process($untrusted->withHeader('X-Forwarded-For', '198.51.100.4'), $handler);
    }

    /**
     * Resolve the enabled limiter from the actual application middleware queue.
     */
    protected function rateLimiter(): RateLimitMiddleware
    {
        Configure::write('PublicRequestProtection.rateLimitEnabled', true);
        $middleware = new Application(dirname(__DIR__, 2) . '/config')->middleware(new MiddlewareQueue());
        $middleware->seek(2);
        $limiter = $middleware->current();
        $this->assertInstanceOf(RateLimitMiddleware::class, $limiter);

        return $limiter;
    }
}
