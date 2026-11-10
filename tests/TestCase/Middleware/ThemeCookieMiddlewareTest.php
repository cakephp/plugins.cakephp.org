<?php
declare(strict_types=1);

namespace App\Test\TestCase\Middleware;

use App\Island\ThemeToggleIsland;
use App\Middleware\ThemeCookieMiddleware;
use Cake\Http\Cookie\SameSiteEnum;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Tests the island's preference reaching the CakePHP response cookie.
 */
class ThemeCookieMiddlewareTest extends TestCase
{
    /**
     * Both directions use the request cookie and leave no pending session value.
     */
    public function testToggleSetsPersistentHttpOnlyCookie(): void
    {
        foreach (['cakephp' => 'cakephp-dark', 'cakephp-dark' => 'cakephp'] as $current => $expected) {
            $request = new ServerRequest(['cookies' => ['theme' => $current]]);
            $handler = $this->createMock(RequestHandlerInterface::class);
            $handler->expects($this->once())->method('handle')->with($request)->willReturnCallback(
                function (ServerRequest $request) use ($expected): Response {
                    $island = new ThemeToggleIsland()->setRequest($request);
                    $island->toggle();
                    $this->assertSame([['name' => 'theme-changed', 'detail' => ['theme' => $expected]]], $island->events());

                    return new Response();
                },
            );

            $response = new ThemeCookieMiddleware()->process($request, $handler);
            $this->assertInstanceOf(Response::class, $response);
            $cookie = $response->getCookieCollection()->get('theme');
            $this->assertSame($expected, $cookie->getValue());
            $this->assertTrue($cookie->isHttpOnly());
            $this->assertSame(SameSiteEnum::LAX, $cookie->getSameSite());
            $this->assertSame('/', $cookie->getPath());
            $this->assertGreaterThan(time() + 300 * 86400, $cookie->getExpiresTimestamp());
            $this->assertNull($request->getSession()->read('Theme.pending'));
        }
    }

    /**
     * A failed update consumes its pending preference without saving it.
     */
    public function testFailedUpdateDoesNotSetCookie(): void
    {
        $request = new ServerRequest();
        $request->getSession()->write('Theme.pending', 'cakephp-dark');
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->willReturn(new Response(['status' => 422]));

        $response = new ThemeCookieMiddleware()->process($request, $handler);
        $this->assertFalse($response->hasHeader('Set-Cookie'));
        $this->assertNull($request->getSession()->read('Theme.pending'));
    }
}
