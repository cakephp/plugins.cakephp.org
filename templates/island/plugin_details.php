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
    <div id="plugin-details-error" class="flex min-h-64 flex-col items-center justify-center gap-4 p-8 text-center text-muted" role="alert" x-show="$server.error" x-cloak>
        <p x-show="$server.error && $server.error.status === 429"><?= __('Too many requests. Please wait a minute before opening another plugin.') ?></p>
        <p x-show="$server.error && $server.error.status !== 429"><?= __('We couldn’t load this plugin.') ?></p>
        <div class="flex flex-wrap justify-center gap-3">
            <button type="button" class="btn btn-sm btn-outline" x-show="$server.error && $server.error.recovery === 'retry'" :disabled="$server.pending()" @click="retry"><?= __('Retry') ?></button>
            <a href="" class="btn btn-sm btn-outline" x-show="$server.error && $server.error.recovery === 'reload'"><?= __('Reload page') ?></a>
            <a :href="url" class="btn btn-sm btn-ghost"><?= __('Open plugin page') ?></a>
        </div>
    </div>
    <div id="plugin-details-content" x-show="!$server.pending() && !$server.error">
        <?php if ($package !== null) : ?>
            <?= $this->element('Packages/plugin-details', compact('package')) ?>
        <?php endif; ?>
    </div>
</div>
