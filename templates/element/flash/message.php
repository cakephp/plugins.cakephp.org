<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 * @var string $type
 */
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<div class="alert flash-message site-width flash-<?= h($type) ?>" role="<?= $type === 'error' ? 'alert' : 'status' ?>" x-data="flashMessage" x-show="open">
    <p><?= $message ?></p>
    <button type="button" class="icon-button btn btn-square btn-ghost" @click="close()" aria-label="<?= __('Dismiss message') ?>" x-cloak>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="m4 4 8 8M12 4l-8 8" stroke-linecap="round"></path>
        </svg>
    </button>
</div>
