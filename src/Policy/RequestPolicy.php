<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Enum\UserRole;
use App\Model\Table\UsersTable;
use Authorization\Exception\MissingIdentityException;
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
        if (!$identity instanceof IdentityInterface) {
            throw new MissingIdentityException(['identity']);
        }

        return $this->fetchTable(UsersTable::class)->exists([
            'id' => $identity['id'],
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);
    }
}
