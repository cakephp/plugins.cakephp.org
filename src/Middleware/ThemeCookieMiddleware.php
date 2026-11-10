<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Http\Cookie\Cookie;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Adds the theme island's pending preference to the normal CakePHP response.
 */
class ThemeCookieMiddleware implements MiddlewareInterface
{
    /**
     * Consume the request's preference once; the cookie remains the source of truth.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if (!$request instanceof ServerRequest) {
            return $response;
        }

        $session = $request->getSession();
        $theme = $session->consume('Theme.pending');
        if (!$response instanceof Response || $response->getStatusCode() >= 400 || !in_array($theme, ['cakephp', 'cakephp-dark'], true)) {
            return $response;
        }

        return $response->withCookie(Cookie::create('theme', $theme, [
            'expires' => '+1 year',
            'path' => '/',
            'secure' => $request->getUri()->getScheme() === 'https',
            'httponly' => true,
            'samesite' => Cookie::SAMESITE_LAX,
        ]));
    }
}
