<?php
declare(strict_types=1);

namespace Admin\Test\TestCase\Controller;

use App\Model\Enum\UserRole;
use App\Model\Table\UsersTable;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class AdminControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = ['app.Users', 'app.FeaturedPlugins', 'app.Packages'];

    /**
     * Reuse the existing sign-in page and preserve the requested admin URL.
     */
    public function testGuestRedirect(): void
    {
        $this->get('/admin/users');
        $this->assertRedirect('/?redirect=%2Fadmin%2Fusers');
    }

    /**
     * Signed-in ordinary users receive 403, rather than a login loop.
     */
    public function testUserForbidden(): void
    {
        $this->session(['AuthId' => 2]);
        $this->get('/admin/users');
        $this->assertResponseCode(403);
    }

    /**
     * Plugin templates mount islands against Interact's standard endpoint.
     */
    public function testAdminPages(): void
    {
        $this->session(['AuthId' => 1]);
        $this->get('/admin/users');
        $this->assertResponseOk();
        $this->assertResponseContains('Admin navigation');
        $this->assertResponseContains('/_interact/update');
        $this->assertResponseContains('member@example.com');
        $this->get('/admin/featured-plugins');
        $this->assertResponseOk();
        $this->assertResponseContains('markstory/asset_compress');
    }

    /**
     * ID-only authentication reloads role and active status on each request.
     */
    public function testStaleRoleAndSuspension(): void
    {
        $this->session(['AuthId' => 1]);
        $this->get('/admin/users');
        $this->assertResponseOk();
        $table = $this->fetchTable(UsersTable::class);
        $table->updateAll(['role' => UserRole::User], ['id' => 1]);
        $this->get('/admin/users');
        $this->assertResponseCode(403);
        $table->updateAll([
            'role' => UserRole::Admin,
            'is_active' => false,
        ], ['id' => 1]);
        $this->get('/admin/users');
        $this->assertRedirect('/?redirect=%2Fadmin%2Fusers');
    }

    /**
     * Full-identity sessions from the old authenticator are never interpreted as IDs.
     */
    public function testLegacySessionRequiresSignIn(): void
    {
        $this->session([
            'Auth' => [
                'id' => 1,
                'role' => 'admin',
                'is_active' => true,
            ],
        ]);
        $this->get('/admin/users');
        $this->assertRedirect('/?redirect=%2Fadmin%2Fusers');
    }

    /**
     * Deleted accounts cannot continue using an existing authenticated session.
     */
    public function testDeletedUserCannotAccessAdmin(): void
    {
        $this->session(['AuthId' => 1]);
        $this->fetchTable(UsersTable::class)->deleteAll(['id' => 1]);
        $this->get('/admin/users');
        $this->assertRedirect('/?redirect=%2Fadmin%2Fusers');
    }

    /**
     * The existing logout action clears the native ID-only session.
     */
    public function testLogoutClearsIdentity(): void
    {
        $this->session(['AuthId' => 1]);
        $this->get('/users/logout');
        $this->assertRedirect('/');
        $this->assertSession(null, 'AuthId');
    }

    /**
     * Even authenticated admin updates require CSRF.
     */
    public function testCsrf(): void
    {
        $this->session(['AuthId' => 1]);
        $this->post('/_interact/update', []);
        $this->assertResponseCode(403);
    }
}
