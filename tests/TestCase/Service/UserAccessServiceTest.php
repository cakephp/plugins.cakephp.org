<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Enum\UserRole;
use App\Model\Table\UsersTable;
use App\Service\UserAccessService;
use Cake\TestSuite\TestCase;

class UserAccessServiceTest extends TestCase
{
    protected array $fixtures = ['app.Users'];

    /**
     * Both access fields change atomically, including console bootstrap.
     */
    public function testPromoteSuspendAndReactivate(): void
    {
        $users = $this->fetchTable(UsersTable::class);
        $service = new UserAccessService($users);
        $user = $service->update(2, UserRole::Admin, true, 1);
        $this->assertSame(UserRole::Admin, $user->role);
        $this->assertFalse($service->update(2, UserRole::User, false, 1)->is_active);
        $this->assertTrue($service->update(2, UserRole::User, true)->is_active);
    }

    /**
     * The console cannot remove the final active administrator.
     */
    public function testProtectsLastAdmin(): void
    {
        $this->expectExceptionMessage('At least one active administrator');
        new UserAccessService($this->fetchTable(UsersTable::class))->update(1, UserRole::User, true);
    }

    /**
     * Even with another admin available, self-suspension is rejected in HTTP.
     */
    public function testProtectsSelf(): void
    {
        $service = new UserAccessService($this->fetchTable(UsersTable::class));
        $service->update(2, UserRole::Admin, true);
        $this->expectExceptionMessage('your own account');
        $service->update(1, UserRole::User, false, 1);
    }

    /**
     * Revalidate the actor inside the transaction, not only on page access.
     */
    public function testRejectsNonAdminActor(): void
    {
        $this->expectExceptionMessage('Administrator access is required');
        new UserAccessService($this->fetchTable(UsersTable::class))->update(3, UserRole::Admin, true, 2);
    }
}
