<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var \Cake\Datasource\Paging\PaginatedInterface $entries
 * @var iterable<\App\Model\Entity\Package> $matches
 * @var \App\Model\Entity\Package|null $previewPackage
 * @var \App\Model\Entity\FeaturedPlugin|null $removingEntry
 * @var string $search
 * @var string $status
 * @var bool $editing
 * @var int|null $editingId
 * @var string $package
 * @var string $packageSearch
 * @var bool $enabled
 * @var string $logo
 * @var string|null $previewLogo
 * @var string $title
 * @var string $description
 * @var string $message
 * @var array<string, list<string>> $errors
 */
use App\Model\Enum\FeaturedPluginStatus;

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
            <input type="search" value="<?= h($search) ?>" placeholder="<?= __('Search featured plugins') ?>" maxlength="100" x-interact:model.live="search">
        </label>
        <select class="select w-auto" aria-label="<?= __('Filter by status') ?>" x-interact:model.live="status">
            <?php foreach (['' => __('All statuses')] + $this->Form->enumOptions(FeaturedPluginStatus::class) as $value => $label) : ?>
                <option value="<?= h($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= h($label) ?></option>
            <?php endforeach; ?>
        </select>
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
        <dialog open class="modal" x-data="adminDialog" @cancel.prevent="$server.call('cancel')" aria-labelledby="featured-editor-title">
            <div class="modal-box max-h-[90dvh] max-w-2xl">
                <h2 id="featured-editor-title" class="mb-5 text-xl font-bold"><?= $editingId === null ? __('Add featured plugin') : __('Edit featured plugin') ?></h2>
                <?= $this->element('Admin.feedback', ['message' => '', 'errors' => $errors]) ?>
                <?php if ($package === '') : ?>
                    <label for="package-search" class="label mb-2"><?= __('Find a catalogue plugin') ?></label>
                    <input id="package-search" class="input w-full" type="search" value="<?= h($packageSearch) ?>" maxlength="100"
                           x-interact:model.live="packageSearch" placeholder="vendor/plugin" autofocus>
                    <ul class="menu mt-3 w-full rounded-box bg-base-200">
                        <?php foreach ($matches as $match) : ?>
                            <li><button type="button" @click="$server.call('selectPackage', <?= (int)$match->id ?>)" :disabled="$server.pending()"><?= h($match->package) ?></button></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="modal-action"><button type="button" class="btn" @click="$server.call('cancel')"><?= __('Cancel') ?></button></div>
                <?php else : ?>
                    <p class="mb-4 font-mono text-sm"><?= h($package) ?></p>
                    <?= $this->Form->create(null, ['url' => false, 'x-interact:submit' => '$server.call("save")']) ?>
                        <fieldset class="fieldset gap-3">
                            <label class="label"><input type="checkbox" class="toggle toggle-primary" x-interact:model="enabled" <?= $enabled ? 'checked' : '' ?>> <?= __('Enabled') ?></label>
                            <label for="featured-title" class="label"><?= __('Title override') ?></label>
                            <input id="featured-title" class="input w-full" value="<?= h($title) ?>" maxlength="255" x-interact:model.live="title" placeholder="<?= __('Use the plugin name') ?>">
                            <label for="featured-description" class="label"><?= __('Description override') ?></label>
                            <textarea id="featured-description" class="textarea w-full" rows="3" maxlength="2000" x-interact:model.live="description"
                                      placeholder="<?= __('Use the imported description') ?>"><?= h($description) ?></textarea>
                            <label for="featured-logo" class="label"><?= __('Logo URL or path') ?></label>
                            <input id="featured-logo" class="input w-full" value="<?= h($logo) ?>" maxlength="2048" x-interact:model.live="logo" placeholder="https://… or /img/…">
                            <p class="text-sm opacity-60"><?= __('Leave overrides blank to use catalogue values. Missing logos use a name-based emblem.') ?></p>
                        </fieldset>
                        <section class="mt-5 rounded-box border border-base-300 bg-base-200 p-5" aria-label="<?= __('Card preview') ?>">
                            <p class="mb-3 text-xs font-semibold uppercase opacity-60"><?= __('Card preview') ?></p>
                            <?= $this->element('Packages/plugin-avatar', ['packageName' => $package, 'logo' => $previewLogo, 'plugin' => false]) ?>
                            <p class="mt-3 text-xl font-bold"><?= h($title !== '' ? $title : substr($package, (int)strpos($package, '/') + 1)) ?></p>
                            <p class="mt-2 text-sm opacity-70"><?= h($description !== '' ? $description : ($previewPackage->description ?? __('Plugin unavailable in the catalogue.'))) ?></p>
                        </section>
                        <div class="modal-action flex-wrap">
                            <button type="button" class="btn" @click="$server.call('cancel')" :disabled="$server.pending()"><?= __('Cancel') ?></button>
                            <button type="submit" class="btn btn-primary" :disabled="$server.pending()"><?= __('Save featured plugin') ?></button>
                        </div>
                    <?= $this->Form->end() ?>
                <?php endif; ?>
            </div>
        </dialog>
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
