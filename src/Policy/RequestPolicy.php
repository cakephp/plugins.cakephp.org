<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Enum\UserRole;
use Authorization\Exception\MissingIdentityException;
use Authorization\IdentityInterface;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Http\ServerRequest;

/**
 * Public routes keep their existing authentication requirements.
 */
class RequestPolicy implements RequestPolicyInterface
{
    /**
     * Use the current identity refreshed by PrimaryKeySession for admin access.
     */
    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool
    {
        if ($request->getParam('plugin') !== 'Admin') {
            return true;
        }
        if (!$identity instanceof IdentityInterface) {
            throw new MissingIdentityException(['identity']);
        }

        return $identity['role'] === UserRole::Admin && $identity['is_active'] === true;
    }
}
