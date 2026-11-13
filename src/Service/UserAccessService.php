<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use App\Model\Enum\UserRole;
use App\Model\Table\UsersTable;
use Cake\Database\Driver\Sqlite;
use DomainException;

/**
 * Applies the same access invariants from HTTP islands and the console.
 */
class UserAccessService
{
    /**
     * Use the host application's user table in both console and HTTP workflows.
     */
    public function __construct(private readonly UsersTable $users)
    {
    }

    /**
     * Serialize access edits so concurrent demotions cannot remove all admins.
     */
    public function update(int $id, UserRole $role, bool $active, ?int $actorId = null): User
    {
        return $this->users->getConnection()->transactional(function () use ($id, $role, $active, $actorId): User {
            // SQLite serializes writers; on MariaDB lock the same rows in ID order.
            $query = $this->users->find()->orderByAsc('Users.id');
            if ($this->users->getConnection()->getDriver() instanceof Sqlite) {
                $this->users->updateAll(['id' => $id], ['id' => $id]);
            } else {
                $query->epilog('FOR UPDATE');
            }
            $accounts = $query->all()->indexBy('id')->toArray();
            $user = $accounts[$id] ?? null;
            if (!$user instanceof User) {
                throw new DomainException('User not found.');
            }
            if ($actorId !== null) {
                $actor = $accounts[$actorId] ?? null;
                if (!$actor instanceof User || $actor->role !== UserRole::Admin || !$actor->is_active) {
                    throw new DomainException('Administrator access is required.');
                }
                if ($actorId === $id && ($role !== UserRole::Admin || !$active)) {
                    throw new DomainException('You cannot demote or suspend your own account.');
                }
            }
            $otherAdmins = array_filter($accounts, static fn(User $account): bool => $account->id !== $id && $account->role === UserRole::Admin && $account->is_active);
            if ($user->role === UserRole::Admin && $user->is_active && ($role !== UserRole::Admin || !$active) && $otherAdmins === []) {
                throw new DomainException('At least one active administrator must remain.');
            }
            $user->set('role', $role)->set('is_active', $active);

            return $this->users->saveOrFail($user);
        });
    }
}
