<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Enum\UserRole;
use App\Model\Enum\UserStatus;
use App\Model\Table\UsersTable;
use App\Service\UserAccessService;
use Cake\Command\Command;
use Cake\Console\ConsoleOptionParser;
use DomainException;

/**
 * Bootstrap and recover administrator access using an existing GitHub account.
 */
class UsersCommand extends Command
{
    /**
     * Select an account and optional noninteractive access changes.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser->setDescription('View or change a GitHub user role and suspension status.')
            ->addArgument('username', ['required' => true, 'help' => 'Existing GitHub username.'])
            ->addOption('role', ['choices' => array_column(UserRole::cases(), 'value'), 'help' => 'Set the account role.'])
            ->addOption('active', ['choices' => ['yes', 'no'], 'help' => 'Reactivate (yes) or suspend (no).']);
    }

    /**
     * Explicit options are idempotent; without options, prompt with current values.
     */
    public function execute(): int
    {
        $users = $this->fetchTable(UsersTable::class);
        $user = $users->find()->where(['username' => $this->args->getArgument('username')])->first();
        if ($user === null) {
            $this->io->err('User not found. Sign in with GitHub first.');

            return self::CODE_ERROR;
        }
        $this->io->out(sprintf('%s: %s, %s', $user->username, $user->role->value, UserStatus::fromActive($user->is_active)->value));
        $role = $this->args->getOption('role');
        $active = $this->args->getOption('active');
        if ($role === null && $active === null) {
            $role = $this->io->askChoice('Role', array_column(UserRole::cases(), 'value'), $user->role->value);
            $active = $this->io->askChoice('Active', ['yes', 'no'], $user->is_active ? 'yes' : 'no');
        }
        if ($role !== null && !is_string($role)) {
            $this->io->err('Role must be a named role.');

            return self::CODE_ERROR;
        }
        try {
            $user = new UserAccessService($users)->update((int)$user->id, $role !== null ? UserRole::from($role) : $user->role, $active === null ? $user->is_active : $active === 'yes');
        } catch (DomainException $exception) {
            $this->io->err($exception->getMessage());

            return self::CODE_ERROR;
        }
        $this->io->out(sprintf('Saved %s: %s, %s', $user->username, $user->role->value, UserStatus::fromActive($user->is_active)->value));

        return self::CODE_SUCCESS;
    }
}
