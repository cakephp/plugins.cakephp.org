<?php
declare(strict_types=1);

namespace Admin\Island;

use App\Model\Enum\UserRole;
use App\Model\Enum\UserStatus;
use App\Model\Table\UsersTable;
use App\Service\UserAccessService;
use Cake\Http\Exception\BadRequestException;
use DomainException;
use Interact\Attribute\Action;
use Interact\Attribute\Pagination;
use Interact\Attribute\State;
use Interact\Pagination\PaginationState;
use Interact\UpdatedPaths;

class UsersIsland extends AdminIsland
{
    #[State(writable: true, url: true)]
    public string $search = '';

    #[State(writable: true, url: true)]
    public string $roleFilter = '';

    #[State(writable: true, url: true)]
    public string $status = '';

    #[Pagination(settings: ['limit' => 20, 'order' => ['Users.username' => 'asc']], url: ['page' => 'page'])]
    public PaginationState $paging;

    #[State]
    public ?int $editingId = null;

    #[State(writable: true)]
    public string $role = UserRole::User->value;

    #[State(writable: true)]
    public bool $active = true;

    #[State]
    public bool $confirming = false;

    #[State]
    public string $message = '';

    /**
     * Filters start at page one; all list queries remain server-side.
     */
    public function updated(UpdatedPaths $paths): void
    {
        if ($paths->has('search') || $paths->has('roleFilter') || $paths->has('status')) {
            $this->paging->page = 1;
        }
        if ($paths->has('role') || $paths->has('active')) {
            $this->confirming = false;
        }
    }

    /**
     * Load a fresh account into a small editable access draft.
     */
    #[Action]
    public function edit(int $id): void
    {
        $user = $this->fetchTable(UsersTable::class)->get($id);
        $this->editingId = $id;
        $this->role = $user->role->value;
        $this->active = $user->is_active;
        $this->confirming = false;
        $this->message = '';
    }

    /**
     * Require a separate confirmation before committing access changes.
     */
    #[Action]
    public function confirm(): void
    {
        if ($this->editingId !== null) {
            $this->confirming = true;
        }
    }

    /**
     * Revalidate target and actor inside the shared transactional service.
     */
    #[Action]
    public function save(): void
    {
        if ($this->editingId === null || !$this->confirming) {
            throw new BadRequestException('Confirm the access change first.');
        }
        $role = UserRole::tryFrom($this->role);
        if ($role === null) {
            $this->setErrors(['role' => ['Choose a valid user role.']]);

            return;
        }
        try {
            new UserAccessService($this->fetchTable(UsersTable::class))->update(
                $this->editingId,
                $role,
                $this->active,
                (int)$this->getRequest()->getAttribute('identity')->getIdentifier(),
            );
        } catch (DomainException $exception) {
            $this->setErrors(['_form' => [$exception->getMessage()]]);

            return;
        }
        $this->cancel();
        $this->message = 'User access updated.';
    }

    /**
     * Discard the open access editor.
     */
    #[Action]
    public function cancel(): void
    {
        $this->editingId = null;
        $this->confirming = false;
    }

    /**
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        $roleFilter = UserRole::tryFrom($this->roleFilter);
        $status = UserStatus::tryFrom($this->status);
        if (mb_strlen($this->search) > 100 || ($this->roleFilter !== '' && $roleFilter === null) || ($this->status !== '' && $status === null)) {
            throw new BadRequestException('Invalid user filters.');
        }
        $table = $this->fetchTable(UsersTable::class);
        $query = $table->find();
        if (trim($this->search) !== '') {
            $search = '%' . trim($this->search) . '%';
            $query->where(['OR' => ['Users.username LIKE' => $search, 'Users.email LIKE' => $search, 'Users.first_name LIKE' => $search, 'Users.last_name LIKE' => $search]]);
        }
        if ($roleFilter !== null) {
            $query->where(['Users.role' => $roleFilter]);
        }
        if ($status !== null) {
            $query->where(['Users.is_active' => $status === UserStatus::Active]);
        }

        return [
            'users' => $this->paginate($query),
            'editingUser' => $this->editingId !== null ? $table->get($this->editingId) : null,
            'actorId' => (int)$this->getRequest()->getAttribute('identity')->getIdentifier(),
        ];
    }
}
