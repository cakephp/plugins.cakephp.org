<?php
declare(strict_types=1);

namespace Admin\Test\TestCase\Island;

use App\Application;
use App\Model\Enum\UserRole;
use App\Model\Table\FeaturedPluginsTable;
use App\Model\Table\UsersTable;
use Authentication\Identity;
use Authorization\Exception\ForbiddenException;
use Authorization\Identity as AuthorizedIdentity;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Interact\TestSuite\IslandTestTrait;

class AdminIslandsTest extends TestCase
{
    use IslandTestTrait;

    protected array $fixtures = ['app.Users', 'app.FeaturedPlugins', 'app.Packages'];

    /**
     * Supply the same native service and decorated identity as HTTP middleware.
     */
    protected function adminRequest(?int $id = 1): ServerRequest
    {
        $request = new ServerRequest(['url' => '/admin/users']);
        $authorization = new Application(CONFIG)->getAuthorizationService($request);
        $identity = $id !== null ? new AuthorizedIdentity($authorization, new Identity($this->fetchTable(UsersTable::class)->get($id))) : null;

        return $request->withAttribute('authorization', $authorization)->withAttribute('identity', $identity);
    }

    /**
     * Search, confirm and persist both access fields through real island updates.
     */
    public function testUserManagement(): void
    {
        $island = $this->testIsland('Admin.Users', request: $this->adminRequest());
        $island->set('search', 'member')->assertSee('member@example.com')->assertDontSee('suspended@example.com')
            ->call('edit', 2)->fill(['role' => 'admin', 'active' => false])->call('confirm')->assertSet('confirming', true)
            ->call('save')->assertSee('User access updated.');
        $user = $this->fetchTable(UsersTable::class)->get(2);
        $this->assertSame(UserRole::Admin, $user->role);
        $this->assertFalse($user->is_active);
    }

    /**
     * Hiding self-edit controls is backed by server-side enforcement.
     */
    public function testSelfEditRejected(): void
    {
        $this->testIsland('Admin.Users', request: $this->adminRequest())->call('edit', 1)
            ->fill(['role' => 'user'])->call('confirm')->call('save')->assertHasErrors(['_form']);
        $this->assertSame(UserRole::Admin, $this->fetchTable(UsersTable::class)->get(1)->role);
    }

    /**
     * Browser state remains scalar and invalid role values cannot reach the service.
     */
    public function testInvalidRoleRejected(): void
    {
        $this->testIsland('Admin.Users', request: $this->adminRequest())->call('edit', 2)
            ->set('role', 'owner')->call('confirm')->call('save')->assertHasErrors(['role']);
        $this->assertSame(UserRole::User, $this->fetchTable(UsersTable::class)->get(2)->role);
    }

    /**
     * The standard update endpoint still rechecks current admin access.
     */
    public function testRevokedSnapshotDenied(): void
    {
        $island = $this->testIsland('Admin.FeaturedPlugins', request: $this->adminRequest());
        $this->fetchTable(UsersTable::class)->updateAll(['role' => 'user'], ['id' => 1]);
        $this->expectException(ForbiddenException::class);
        $island->call('create');
    }

    /**
     * Guessing a plugin island name never grants access to guests.
     */
    public function testGuestCannotMount(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->testIsland('Admin.Users', request: $this->adminRequest(null));
    }

    /**
     * Add, validate, disable and remove entries without package mutation.
     */
    public function testFeaturedManagement(): void
    {
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $island = $this->testIsland('Admin.FeaturedPlugins', request: $this->adminRequest());
        $island->call('create')->set('packageSearch', 'package-01')->assertSee('vendor/package-01')->call('selectPackage', 2)
            ->fill(['title' => 'Our pick', 'description' => 'Editorial description', 'logo' => 'javascript:alert(1)'])->call('save')->assertHasErrors(['logo']);
        $island->set('logo', '/img/pick.svg')->call('save')->assertSee('Featured plugin saved.');
        $entry = $table->find()->where(['package' => 'vendor/package-01'])->firstOrFail();
        $this->assertSame('Our pick', $entry->title);
        $island->call('edit', $entry->id)->set('enabled', false)->call('save');
        $this->assertFalse($table->get($entry->id)->enabled);
        $island->call('confirmRemoval', $entry->id)->assertSee('Confirm removal')->call('remove');
        $this->assertFalse($table->exists(['id' => $entry->id]));
    }

    /**
     * Existing featured packages cannot be added twice.
     */
    public function testDuplicateFeaturedEntry(): void
    {
        $this->testIsland('Admin.FeaturedPlugins', request: $this->adminRequest())->call('create')->call('selectPackage', 1)->call('save')->assertHasErrors(['package']);
    }

    /**
     * Associated catalogue counts sort numerically through the native paginator.
     */
    public function testFeaturedCountSorting(): void
    {
        $island = $this->testIsland('Admin.FeaturedPlugins', request: $this->adminRequest());
        $island->fill(['paging.sort' => 'stars', 'paging.direction' => 'asc']);
        preg_match_all('/data-featured-id="(\d+)"/', $island->result()->html, $matches);
        $this->assertSame(['3', '1', '2'], $matches[1]);
        $island->fill(['paging.sort' => 'downloads', 'paging.direction' => 'desc']);
        preg_match_all('/data-featured-id="(\d+)"/', $island->result()->html, $matches);
        $this->assertSame(['3', '2', '1'], $matches[1]);
    }

    /**
     * Curation remains editable after catalogue deletion.
     */
    public function testUnavailableEntry(): void
    {
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $entry = $table->saveOrFail($table->newEntity(['package' => 'missing/package', 'enabled' => true]));
        $this->testIsland('Admin.FeaturedPlugins', request: $this->adminRequest())->set('status', 'unavailable')
            ->assertSee('missing/package')->assertDontSee('markstory/asset_compress')->call('edit', $entry->id)->set('enabled', false)->call('save');
        $this->assertFalse($table->get($entry->id)->enabled);
    }
}
