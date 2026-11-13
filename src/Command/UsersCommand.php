<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Enum\UserRole;
use App\Model\Enum\UserStatus;
use App\Model\Table\UsersTable;
use App\Service\UserAccessService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
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
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $users = $this->fetchTable(UsersTable::class);
        $user = $users->find()->where(['username' => $args->getArgument('username')])->first();
        if ($user === null) {
            $io->err('User not found. Sign in with GitHub first.');

            return self::CODE_ERROR;
        }
        $io->out(sprintf('%s: %s, %s', $user->username, $user->role->value, UserStatus::fromActive($user->is_active)->value));
        $role = $args->getOption('role');
        $active = $args->getOption('active');
        if ($role === null && $active === null) {
            $role = $io->askChoice('Role', array_column(UserRole::cases(), 'value'), $user->role->value);
            $active = $io->askChoice('Active', ['yes', 'no'], $user->is_active ? 'yes' : 'no');
        }
        try {
            $user = new UserAccessService($users)->update((int)$user->id, $role !== null ? UserRole::from($role) : $user->role, $active === null ? $user->is_active : $active === 'yes');
        } catch (DomainException $exception) {
            $io->err($exception->getMessage());

            return self::CODE_ERROR;
        }
        $io->out(sprintf('Saved %s: %s, %s', $user->username, $user->role->value, UserStatus::fromActive($user->is_active)->value));

        return self::CODE_SUCCESS;
    }
}
