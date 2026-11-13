<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Enum\UserRole;
use App\Model\Table\UsersTable;
use Authorization\IdentityInterface;
use Authorization\Policy\RequestPolicyInterface;
use Cake\Http\ServerRequest;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Public routes keep their existing authentication requirements.
 */
class RequestPolicy implements RequestPolicyInterface
{
    use LocatorAwareTrait;

    /**
     * Administrative routes require an active administrator.
     */
    public function canAccess(?IdentityInterface $identity, ServerRequest $request): bool
    {
        if ($request->getParam('plugin') !== 'Admin') {
            return true;
        }

        return $identity instanceof IdentityInterface && $this->fetchTable(UsersTable::class)->exists([
            'id' => $identity['id'],
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);
    }
}
