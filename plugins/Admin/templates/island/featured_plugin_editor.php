<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var \App\Model\Entity\FeaturedPlugin $entry
 * @var iterable<\App\Model\Entity\Package> $matches
 * @var \App\Model\Entity\Package|null $previewPackage
 * @var int|null $entryId
 * @var string $package
 * @var string $packageSearch
 * @var array<string, list<string>> $errors
 */
$this->Form->templater()->load('Admin.form-templates');
?>
<div>
    <dialog open class="modal" x-data="adminDialog" @cancel.prevent="$server.call('cancel')" aria-labelledby="featured-editor-title">
        <div class="modal-box max-h-[90dvh] max-w-2xl">
            <h2 id="featured-editor-title" class="mb-5 text-xl font-bold"><?= $entryId === null ? __('Add featured plugin') : __('Edit featured plugin') ?></h2>
            <?= $this->element('Admin.feedback', [
                'message' => '',
                'errors' => $errors,
            ]) ?>
            <?php if ($package === '') : ?>
                <?= $this->Form->control('packageSearch', [
                    'id' => 'package-search',
                    'type' => 'search',
                    'class' => 'input w-full',
                    'label' => __('Find a catalogue plugin'),
                    'value' => $packageSearch,
                    'maxlength' => 100,
                    'x-interact:model.live' => 'packageSearch',
                    'placeholder' => 'vendor/plugin',
                    'autofocus' => true,
                ]) ?>
                <ul class="menu mt-3 w-full rounded-box bg-base-200">
                    <?php foreach ($matches as $match) : ?>
                        <li><button type="button" @click="$server.call('selectPackage', <?= (int)$match->id ?>)" :disabled="$server.pending()"><?= h($match->package) ?></button></li>
                    <?php endforeach; ?>
                </ul>
                <div class="modal-action"><button type="button" class="btn" @click="$server.call('cancel')"><?= __('Cancel') ?></button></div>
            <?php else : ?>
                <p class="mb-4 font-mono text-sm"><?= h($package) ?></p>
                <?= $this->Form->create($entry, [
                    'url' => false,
                    'x-interact:model' => 'draft',
                    'x-interact:submit' => '$server.call("save")',
                ]) ?>
                    <fieldset class="space-y-3">
                        <?= $this->Form->control('enabled', [
                            'type' => 'checkbox',
                            'class' => 'toggle toggle-primary',
                            'label' => [
                                'text' => __('Enabled'),
                                'class' => 'label',
                            ],
                            'hiddenField' => false,
                        ]) ?>
                        <?= $this->Form->control('title', [
                            'id' => 'featured-title',
                            'class' => 'input w-full',
                            'label' => __('Title override'),
                            'maxlength' => 255,
                            'x-interact:model.live' => '',
                            'placeholder' => __('Use the plugin name'),
                        ]) ?>
                        <?= $this->Form->control('description', [
                            'id' => 'featured-description',
                            'type' => 'textarea',
                            'class' => 'textarea w-full',
                            'label' => __('Description override'),
                            'rows' => 3,
                            'maxlength' => 2000,
                            'x-interact:model.live' => '',
                            'placeholder' => __('Use the imported description'),
                        ]) ?>
                        <?= $this->Form->control('logo', [
                            'id' => 'featured-logo',
                            'class' => 'input w-full',
                            'label' => __('Logo URL or path'),
                            'maxlength' => 2048,
                            'x-interact:model.live' => '',
                            'placeholder' => 'https://… or /img/…',
                        ]) ?>
                        <p class="text-sm opacity-60"><?= __('Leave overrides blank to use catalogue values. Missing logos use a name-based emblem.') ?></p>
                    </fieldset>
                    <section class="mt-5" aria-label="<?= __('Card preview') ?>">
                        <p class="mb-3 text-xs font-semibold uppercase opacity-60"><?= __('Card preview') ?></p>
                        <div class="community-pick-row rounded-box border border-base-300 p-4 md:p-5">
                            <div class="package-identity">
                                <?= $this->element('Packages/plugin-avatar', [
                                    'packageName' => $package,
                                    'logo' => $entry->logo,
                                    'plugin' => false,
                                ]) ?>
                                <div class="min-w-0">
                                    <span class="community-pick-badge">
                                        <?= $this->element('Packages/community_pick_icon') ?> <?= __('Community pick') ?>
                                    </span>
                                    <p class="package-name"><?= h($package) ?></p>
                                    <?php if ($entry->title !== null) : ?>
                                        <p class="community-pick-title"><?= h($entry->title) ?></p>
                                    <?php endif; ?>
                                    <p class="line-clamp-2 text-[0.9375rem] wrap-anywhere">
                                        <?= h($entry->description ?? $previewPackage->description ?? __('Plugin unavailable in the catalogue.')) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>
                    <div class="modal-action flex-wrap">
                        <button type="button" class="btn" @click="$server.call('cancel')" :disabled="$server.pending()"><?= __('Cancel') ?></button>
                        <?= $this->Form->button(__('Save featured plugin'), [
                            'class' => 'btn btn-primary',
                            ':disabled' => '$server.pending()',
                        ]) ?>
                    </div>
                <?= $this->Form->end() ?>
            <?php endif; ?>
        </div>
    </dialog>
</div>
