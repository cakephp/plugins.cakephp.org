<?php
/**
 * @var \App\View\AppView|\App\View\IslandTemplateView $this
 * @var string $command
 * @var string $id
 */
?>
<div class="plugin-install" x-data="packageInstall" id="<?= h($id) ?>">
    <div class="plugin-install-command">
        <code x-ref="command"><?= h($command) ?></code>
        <button type="button" class="btn btn-sm btn-ghost" @click="copy" :aria-label="copied ? '<?= __('Command copied') ?>' : '<?= __('Copy install command') ?>'">
            <span x-show="!copied"><?= __('Copy') ?></span>
            <span x-show="copied" x-cloak><?= __('Copied') ?></span>
        </button>
    </div>
    <p :class="failed ? 'plugin-copy-feedback' : 'sr-only'" role="status" x-show="copied || failed" x-cloak
       x-text="failed ? '<?= __('Select the command above to copy it.') ?>' : '<?= __('Install command copied to clipboard.') ?>'"></p>
</div>
