<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Model\Enum\UserRole;
use App\Model\Table\UsersTable;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

class UsersCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    protected array $fixtures = ['app.Users'];

    /**
     * Idempotent flags support first-admin bootstrap and access recovery.
     */
    public function testChangesAccess(): void
    {
        $this->exec('users member --role admin --active yes');
        $this->assertExitSuccess();
        $this->assertOutputContains('Saved member: admin, active');
        $this->assertSame(UserRole::Admin, $this->fetchTable(UsersTable::class)->get(2)->role);
    }

    /**
     * With no flags, prompt for both fields using current defaults.
     */
    public function testInteractiveReactivation(): void
    {
        $this->exec('users suspended', ['user', 'yes']);
        $this->assertExitSuccess();
        $this->assertTrue($this->fetchTable(UsersTable::class)->get(3)->is_active);
    }

    /**
     * Unknown accounts cannot be implicitly created by the command.
     */
    public function testUnknownUser(): void
    {
        $this->exec('users nobody --role admin');
        $this->assertExitError();
        $this->assertErrorContains('User not found');
    }

    /**
     * Console and HTTP share the last-admin invariant.
     */
    public function testLastAdmin(): void
    {
        $this->exec('users admin --active no');
        $this->assertExitError();
        $this->assertTrue($this->fetchTable(UsersTable::class)->get(1)->is_active);
    }
}
