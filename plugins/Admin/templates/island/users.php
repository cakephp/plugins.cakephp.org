<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var \Cake\Datasource\Paging\PaginatedInterface $users
 * @var \App\Model\Entity\User|null $editingUser
 * @var int $actorId
 * @var string $search
 * @var string $roleFilter
 * @var string $status
 * @var string $role
 * @var bool $active
 * @var bool $confirming
 * @var string $message
 * @var array<string, list<string>> $errors
 */
use App\Model\Enum\UserRole;
use App\Model\Enum\UserStatus;

?>
<div>
    <h1 class="mb-2 text-3xl font-bold"><?= __('Users') ?></h1>
    <p class="mb-6 opacity-70"><?= __('Manage roles and access for GitHub accounts.') ?></p>
    <?= $this->element('Admin.feedback', compact('message', 'errors')) ?>
    <div class="mb-5 flex flex-wrap gap-3">
        <label class="input min-w-48 flex-1">
            <span class="sr-only"><?= __('Search users') ?></span>
            <input type="search" value="<?= h($search) ?>" placeholder="<?= __('Search username, name or email') ?>" maxlength="100" x-interact:model.live="search">
        </label>
        <select class="select w-auto" aria-label="<?= __('Filter by role') ?>" x-interact:model.live="roleFilter">
            <?php foreach (['' => __('All roles')] + $this->Form->enumOptions(UserRole::class) as $value => $label) : ?>
                <option value="<?= h($value) ?>" <?= $roleFilter === $value ? 'selected' : '' ?>><?= h($label) ?></option>
            <?php endforeach; ?>
        </select>
        <select class="select w-auto" aria-label="<?= __('Filter by status') ?>" x-interact:model.live="status">
            <?php foreach (['' => __('All statuses')] + $this->Form->enumOptions(UserStatus::class) as $value => $label) : ?>
                <option value="<?= h($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= h($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
        <table class="table">
            <thead><tr>
                <th><?= __('Account') ?></th><th><?= __('Email') ?></th><th><?= __('Role') ?></th>
                <th><?= __('Status') ?></th><th><?= __('Joined') ?></th><th><span class="sr-only"><?= __('Actions') ?></span></th>
            </tr></thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                    <tr data-user-id="<?= (int)$user->id ?>">
                        <td><span class="font-semibold">@<?= h($user->username) ?></span><div class="text-sm opacity-60"><?= h(trim($user->first_name . ' ' . $user->last_name)) ?></div></td>
                        <td><?= h($user->email) ?></td>
                        <td><span class="badge badge-sm badge-soft <?= $user->role === UserRole::Admin ? 'badge-primary' : 'badge-neutral' ?>"><?= h($user->role->label()) ?></span></td>
                        <td><span class="badge badge-sm badge-soft <?= $user->is_active ? 'badge-success' : 'badge-warning' ?>"><?= h(UserStatus::fromActive($user->is_active)->label()) ?></span></td>
                        <td class="whitespace-nowrap"><?= h($user->created->format('Y-m-d')) ?></td>
                        <td>
                            <?php if ($actorId === $user->id) : ?>
                                <span class="text-sm opacity-60"><?= __('Your account') ?></span>
                            <?php else : ?>
                                <button type="button" class="btn btn-ghost btn-sm" @click="$server.call('edit', <?= (int)$user->id ?>)"
                                        :disabled="$server.pending()"><?= __('Manage access') ?></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($users) === 0) :
                    ?><tr><td colspan="6" class="py-10 text-center opacity-60"><?= __('No users found.') ?></td></tr><?php
                endif; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('Admin.paging') ?>
    <?php if ($editingUser !== null) : ?>
        <dialog open class="modal" x-data="adminDialog" @cancel.prevent="$server.call('cancel')" aria-labelledby="user-editor-title">
            <div class="modal-box">
                <h2 id="user-editor-title" class="mb-5 text-xl font-bold"><?= __('Manage access for @{0}', h($editingUser->username)) ?></h2>
                <?= $this->element('Admin.feedback', ['message' => '', 'errors' => $errors]) ?>
                <?php if ($confirming) : ?>
                    <p><?= __('Set this account to {0} and {1}?', h(UserRole::tryFrom($role)?->label() ?? $role), h(UserStatus::fromActive($active)->label())) ?></p>
                    <div class="modal-action">
                        <button type="button" class="btn" @click="$server.call('cancel')" :disabled="$server.pending()"><?= __('Cancel') ?></button>
                        <button type="button" class="btn btn-primary" @click="$server.call('save')" :disabled="$server.pending()"><?= __('Confirm change') ?></button>
                    </div>
                <?php else : ?>
                    <?= $this->Form->create(null, ['url' => false, 'x-interact:submit' => '$server.call("confirm")']) ?>
                        <fieldset class="fieldset">
                            <label for="user-role" class="label"><?= __('Role') ?></label>
                            <select id="user-role" class="select w-full" x-interact:model="role">
                                <?php foreach ($this->Form->enumOptions(UserRole::class) as $value => $label) : ?>
                                    <option value="<?= h($value) ?>" <?= $role === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label class="label mt-4">
                                <input type="checkbox" class="toggle toggle-primary" x-interact:model="active" <?= $active ? 'checked' : '' ?>> <?= __('Account active') ?>
                            </label>
                        </fieldset>
                        <div class="modal-action">
                            <button type="button" class="btn" @click="$server.call('cancel')" :disabled="$server.pending()"><?= __('Cancel') ?></button>
                            <button type="submit" class="btn btn-primary" :disabled="$server.pending()"><?= __('Review change') ?></button>
                        </div>
                    <?= $this->Form->end() ?>
                <?php endif; ?>
            </div>
        </dialog>
    <?php endif; ?>
</div>
