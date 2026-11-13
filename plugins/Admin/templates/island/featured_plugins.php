<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var \Cake\Datasource\Paging\PaginatedInterface $entries
 * @var \App\Model\Entity\FeaturedPlugin|null $removingEntry
 * @var string $search
 * @var string $status
 * @var bool $editing
 * @var int|null $editingId
 * @var string $message
 * @var array<string, list<string>> $errors
 */
use App\Model\Enum\FeaturedPluginStatus;

$this->Form->templater()->load('Admin.form-templates');
?>
<div>
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div><h1 class="mb-2 text-3xl font-bold"><?= __('Featured plugins') ?></h1><p class="opacity-70"><?= __('The homepage randomly shows up to three enabled, available plugins.') ?></p></div>
        <button type="button" class="btn btn-primary" @click="$server.call('create')" :disabled="$server.pending()"><?= __('Add featured plugin') ?></button>
    </div>
    <?= $this->element('Admin.feedback', compact('message', 'errors')) ?>
    <div class="mb-5 flex flex-wrap gap-3">
        <label class="input min-w-48 flex-1">
            <span class="sr-only"><?= __('Search featured plugins') ?></span>
            <?= $this->Form->search('search', [
                'value' => $search,
                'placeholder' => __('Search featured plugins'),
                'maxlength' => 100,
                'x-interact:model.live' => 'search',
            ]) ?>
        </label>
        <?= $this->Form->select('status', $this->Form->enumOptions(FeaturedPluginStatus::class), [
            'class' => 'select w-auto',
            'aria-label' => __('Filter by status'),
            'empty' => __('All statuses'),
            'value' => $status,
            'x-interact:model.live' => 'status',
        ]) ?>
    </div>
    <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
        <table class="table">
            <thead><tr>
                <th><?= $this->Paginator->sort('package', __('Plugin')) ?></th><th><?= $this->Paginator->sort('title', __('Featured title')) ?></th>
                <th class="text-right"><?= $this->Paginator->sort('stars', __('GitHub stars')) ?></th>
                <th class="text-right"><?= $this->Paginator->sort('downloads', __('Downloads')) ?></th>
                <th><?= $this->Paginator->sort('enabled', __('Status')) ?></th><th><span class="sr-only"><?= __('Actions') ?></span></th>
            </tr></thead>
            <tbody>
                <?php foreach ($entries as $entry) : ?>
                    <tr data-featured-id="<?= (int)$entry->id ?>">
                        <td class="font-semibold"><?= h($entry->package) ?></td>
                        <td><?= h($entry->title ?? '—') ?></td>
                        <td class="text-right tabular-nums"><?= $entry->catalogue_package !== null ? $this->Number->format($entry->catalogue_package->stars) : '—' ?></td>
                        <td class="text-right tabular-nums"><?= $entry->catalogue_package !== null ? $this->Number->format($entry->catalogue_package->downloads) : '—' ?></td>
                        <td>
                            <span class="badge badge-sm badge-soft <?= $entry->enabled ? 'badge-success' : 'badge-neutral' ?>">
                                <?= h(FeaturedPluginStatus::fromEnabled($entry->enabled)->label()) ?>
                            </span>
                            <?php if ($entry->catalogue_package === null) :
                                ?><span class="badge badge-sm badge-soft badge-warning"><?= h(FeaturedPluginStatus::Unavailable->label()) ?></span><?php
                            endif; ?></td>
                        <td class="whitespace-nowrap text-right">
                            <button type="button" class="btn btn-ghost btn-sm" @click="$server.call('edit', <?= (int)$entry->id ?>)" :disabled="$server.pending()"><?= __('Edit') ?></button>
                            <button type="button" class="btn btn-ghost btn-sm text-error" @click="$server.call('confirmRemoval', <?= (int)$entry->id ?>)"
                                    :disabled="$server.pending()"><?= __('Remove') ?></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($entries) === 0) :
                    ?><tr><td colspan="6" class="py-10 text-center opacity-60"><?= __('No featured plugins found.') ?></td></tr><?php
                endif; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('Admin.paging') ?>
    <?php if ($editing) : ?>
        <?= $this->Interact->render('Admin.FeaturedPluginEditor', ['entryId' => $editingId], key: 'featured-editor-' . ($editingId ?? 'new')) ?>
    <?php endif; ?>
    <?php if ($removingEntry !== null) : ?>
        <dialog open class="modal" x-data="adminDialog" @cancel.prevent="$server.call('cancel')" aria-labelledby="remove-title">
            <div class="modal-box">
                <h2 id="remove-title" class="mb-4 text-xl font-bold"><?= __('Remove featured plugin?') ?></h2>
                <p><?= __('Remove {0} from the featured selection?', h($removingEntry->package)) ?></p>
                <div class="modal-action">
                    <button type="button" class="btn" @click="$server.call('cancel')" :disabled="$server.pending()"><?= __('Cancel') ?></button>
                    <button type="button" class="btn btn-error" @click="$server.call('remove')" :disabled="$server.pending()"><?= __('Confirm removal') ?></button>
                </div>
            </div>
        </dialog>
    <?php endif; ?>
</div>
