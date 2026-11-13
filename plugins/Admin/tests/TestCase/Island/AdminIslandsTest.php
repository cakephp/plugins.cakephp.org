<?php
declare(strict_types=1);

namespace Admin\Test\TestCase\Island;

use App\Application;
use App\Model\Enum\UserRole;
use App\Model\Table\FeaturedPluginsTable;
use App\Model\Table\PackagesTable;
use App\Model\Table\UsersTable;
use Authentication\Identity;
use Authorization\Exception\ForbiddenException;
use Authorization\Exception\MissingIdentityException;
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
            ->call('edit', 2)->fill([
                'role' => 'admin',
                'active' => false,
            ])->call('confirm')->assertSet('confirming', true)
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
     * The independently mounted editor checks access on its own updates too.
     */
    public function testRevokedEditorSnapshotDenied(): void
    {
        $island = $this->testIsland('Admin.FeaturedPluginEditor', ['entryId' => 1], request: $this->adminRequest());
        $this->fetchTable(UsersTable::class)->updateAll(['role' => 'user'], ['id' => 1]);
        $this->expectException(ForbiddenException::class);
        $island->set('draft.title', 'Unauthorized change')->call('save');
    }

    /**
     * Guessing a plugin island name never grants access to guests.
     */
    public function testGuestCannotMount(): void
    {
        $this->expectException(MissingIdentityException::class);
        $this->testIsland('Admin.Users', request: $this->adminRequest(null));
    }

    /**
     * Add, validate, disable and remove entries without package mutation.
     */
    public function testFeaturedManagement(): void
    {
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $request = $this->adminRequest();
        $editor = $this->testIsland('Admin.FeaturedPluginEditor', request: $request);
        $editor->set('packageSearch', 'package-01')->assertSee('vendor/package-01')->call('selectPackage', 2)
            ->fill([
                'draft.title' => 'Our pick',
                'draft.description' => 'Editorial description',
                'draft.logo' => 'javascript:alert(1)',
            ])->call('save')->assertHasErrors(['logo']);
        $editor->set('draft.logo', '/img/pick.svg')->call('save')->assertDispatched('featured-plugin-saved');
        $entry = $table->find()->where(['package' => 'vendor/package-01'])->firstOrFail();
        $this->assertSame('Our pick', $entry->title);
        $this->testIsland('Admin.FeaturedPluginEditor', ['entryId' => $entry->id], request: $request)
            ->set('draft.enabled', false)->call('save')->assertDispatched('featured-plugin-saved');
        $this->assertFalse($table->get($entry->id)->enabled);
        $island = $this->testIsland('Admin.FeaturedPlugins', request: $request);
        $island->call('confirmRemoval', $entry->id)->assertSee('Confirm removal')->call('remove');
        $this->assertFalse($table->exists(['id' => $entry->id]));
    }

    /**
     * Existing featured packages cannot be added twice.
     */
    public function testDuplicateFeaturedEntry(): void
    {
        $this->testIsland('Admin.FeaturedPluginEditor', request: $this->adminRequest())
            ->call('selectPackage', 1)->call('save')->assertHasErrors(['package']);
    }

    /**
     * Whole-form drafts still use ORM validation and cannot change the selection.
     */
    public function testFeaturedDraftValidationAndFieldRestrictions(): void
    {
        $editor = $this->testIsland('Admin.FeaturedPluginEditor', ['entryId' => 1], request: $this->adminRequest());
        $editor->fill([
            'draft.logo' => ['invalid'],
            'draft.title' => ['invalid'],
        ])->call('save')->assertHasErrors(['logo', 'title']);
        $editor->fill([
            'draft.logo' => '',
            'draft.title' => 'Editorial title',
            'draft.package' => 'cakedc/users',
            'draft.id' => 2,
        ])->call('save')->assertDispatched('featured-plugin-saved');
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $this->assertSame('markstory/asset_compress', $table->get(1)->package);
        $this->assertSame('Editorial title', $table->get(1)->title);
        $this->assertNull($table->get(2)->title);
    }

    /**
     * Associated catalogue counts sort numerically through the native paginator.
     */
    public function testFeaturedCountSorting(): void
    {
        $island = $this->testIsland('Admin.FeaturedPlugins', request: $this->adminRequest());
        $island->fill([
            'paging.sort' => 'stars',
            'paging.direction' => 'asc',
        ]);
        preg_match_all('/data-featured-id="(\d+)"/', $island->result()->html, $matches);
        $this->assertSame(['3', '1', '2'], $matches[1]);
        $island->fill([
            'paging.sort' => 'downloads',
            'paging.direction' => 'desc',
        ]);
        preg_match_all('/data-featured-id="(\d+)"/', $island->result()->html, $matches);
        $this->assertSame(['3', '2', '1'], $matches[1]);
    }

    /**
     * Curation remains editable after catalogue deletion.
     */
    public function testUnavailableEntry(): void
    {
        $table = $this->fetchTable(FeaturedPluginsTable::class);
        $request = $this->adminRequest();
        $this->fetchTable(PackagesTable::class)->deleteAll(['package' => 'markstory/asset_compress']);
        $this->testIsland('Admin.FeaturedPlugins', request: $request)->set('status', 'unavailable')
            ->assertSee('markstory/asset_compress')->assertDontSee('cakedc/users');
        $this->testIsland('Admin.FeaturedPluginEditor', ['entryId' => 1], request: $request)
            ->set('draft.enabled', false)->call('save')->assertDispatched('featured-plugin-saved');
        $this->assertFalse($table->get(1)->enabled);
    }
}
