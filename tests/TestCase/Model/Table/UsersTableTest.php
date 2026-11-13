<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use ADmad\SocialAuth\Model\Entity\SocialProfile;
use App\Model\Enum\UserRole;
use App\Model\Table\UsersTable;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Session;
use Cake\TestSuite\TestCase;
use Override;

class UsersTableTest extends TestCase
{
    #[Override]
    protected array $fixtures = ['app.Users'];

    /**
     * Neither general patching nor social profile updates can assign access.
     */
    public function testProtectedAccessFieldsAndDefaults(): void
    {
        $users = $this->fetchTable(UsersTable::class);
        $user = $users->newEntity(['email' => 'new@example.com', 'username' => 'new', 'role' => 'admin', 'is_active' => false]);
        $user = $users->get($users->saveOrFail($user)->id);
        $this->assertSame(UserRole::User, $user->role);
        $this->assertTrue($user->is_active);
        $users->getUser(new SocialProfile(['email' => 'admin@example.com', 'username' => 'admin']), new Session());
        $this->assertSame(UserRole::Admin, $users->get(1)->role);
    }

    /**
     * An unlinked GitHub identity matching a suspended email is rejected.
     */
    public function testSuspendedEmailCannotSignIn(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->fetchTable(UsersTable::class)->getUser(new SocialProfile(['email' => 'suspended@example.com', 'username' => 'suspended']), new Session());
    }

    /**
     * The finder used by SocialAuth excludes linked suspended accounts.
     */
    public function testActiveFinder(): void
    {
        $this->assertSame([1, 2], $this->fetchTable(UsersTable::class)->find('active')->orderByAsc('id')->all()->extract('id')->toList());
    }

    /**
     * ORM hydration, persistence and validation use the same backed enum.
     */
    public function testRoleEnumMapping(): void
    {
        $users = $this->fetchTable(UsersTable::class);
        $user = $users->get(2);
        $this->assertSame(UserRole::User, $user->role);
        $user->set('role', UserRole::Admin);
        $users->saveOrFail($user);
        $this->assertTrue($users->exists(['id' => 2, 'role' => UserRole::Admin]));
        $this->assertSame(UserRole::Admin, $users->get(2)->role);
        $this->assertArrayHasKey('role', $users->getValidator()->validate(['role' => 'owner'], false));
    }
}
