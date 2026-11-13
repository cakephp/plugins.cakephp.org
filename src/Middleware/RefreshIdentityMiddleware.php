<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Model\Table\UsersTable;
use Cake\ORM\Locator\LocatorAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Session authentication must never retain revoked roles or suspended accounts.
 */
class RefreshIdentityMiddleware implements MiddlewareInterface
{
    use LocatorAwareTrait;

    /**
     * Reload the account before Authentication consumes the session identity.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $request->getAttribute('session');
        $id = $session->read('Auth.id');
        if ($id !== null) {
            $user = $this->fetchTable(UsersTable::class)->find()->where(['id' => $id, 'is_active' => true])->first();
            if ($user === null) {
                $session->delete('Auth');
                $session->renew();
            } else {
                $session->write('Auth', $user);
            }
        }

        return $handler->handle($request);
    }
}
