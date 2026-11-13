<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Apply the request policy with HTML login redirects and Interact-safe failures.
 */
class AdminAccessMiddleware implements MiddlewareInterface
{
    /**
     * Reuse the public GitHub sign-in and preserve a local admin return URL.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $identity = $request->getAttribute('identity');
        if (!$request->getAttribute('authorization')->can($identity, 'access', $request)) {
            if ($identity === null) {
                if (in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
                    return new Response()->withLocation('/?redirect=' . rawurlencode($request->getRequestTarget()));
                }
                throw new UnauthorizedException('Sign in to continue.');
            }
            throw new ForbiddenException('Administrator access is required.');
        }

        return $handler->handle($request);
    }
}
