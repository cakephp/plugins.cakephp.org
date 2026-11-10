<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var \App\Model\Entity\Package|null $package
 */
?>
<div x-data="pluginDetails" @plugin-open.window="load($event)" :aria-busy="$server.pending()">
    <div id="plugin-details-loading" class="flex min-h-64 flex-wrap items-center justify-center gap-3 p-8 text-muted" role="status" x-show="$server.pending()" x-cloak>
        <span class="loading loading-spinner loading-sm" aria-hidden="true"></span>
        <?= __('Loading plugin details…') ?>
    </div>
    <div id="plugin-details-error" class="flex min-h-64 flex-wrap items-center justify-center gap-3 p-8 text-muted" role="alert" x-show="$server.error" x-cloak>
        <p><?= __('We couldn’t load this plugin. Please try again.') ?></p>
        <button type="button" class="btn btn-sm btn-outline" @click="retry"><?= __('Retry') ?></button>
    </div>
    <div id="plugin-details-content" x-show="!$server.pending() && !$server.error">
        <?php if ($package !== null) : ?>
            <?= $this->element('Packages/plugin-details', compact('package')) ?>
        <?php endif; ?>
    </div>
</div>
