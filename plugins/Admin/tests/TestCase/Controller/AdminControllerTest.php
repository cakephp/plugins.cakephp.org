<?php
declare(strict_types=1);

namespace Admin\Test\TestCase\Controller;

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
        $this->session(['Auth' => ['id' => 2]]);
        $this->get('/admin/users');
        $this->assertResponseCode(403);
    }

    /**
     * Plugin templates mount islands against Interact's standard endpoint.
     */
    public function testAdminPages(): void
    {
        $this->session(['Auth' => ['id' => 1]]);
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
     * A previously privileged session is refreshed before policy evaluation.
     */
    public function testStaleRoleAndSuspension(): void
    {
        $this->session(['Auth' => ['id' => 2, 'role' => 'admin', 'is_active' => true]]);
        $this->get('/admin/users');
        $this->assertResponseCode(403);
        $this->session(['Auth' => ['id' => 3, 'role' => 'admin', 'is_active' => true]]);
        $this->get('/admin/users');
        $this->assertRedirect('/?redirect=%2Fadmin%2Fusers');
    }

    /**
     * Even authenticated admin updates require CSRF.
     */
    public function testCsrf(): void
    {
        $this->session(['Auth' => ['id' => 1]]);
        $this->post('/_interact/update', []);
        $this->assertResponseCode(403);
    }
}
