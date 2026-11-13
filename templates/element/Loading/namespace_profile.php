<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var string $namespace
 * @var string $githubUrl
 */
?>
<div data-namespace-profile-loading>
    <?= $this->element('Namespaces/profile', [
        'namespace' => $namespace,
        'githubUrl' => $githubUrl,
        'profile' => null,
        'loading' => true,
    ]) ?>
    <span class="sr-only" role="status" x-show="!$server.error"><?= __('Loading GitHub profile…') ?></span>
    <div class="mt-3 text-sm text-muted" x-show="$server.error" x-cloak>
        <p role="alert"><?= __('The GitHub profile could not be loaded.') ?></p>
        <?= $this->Form->button(__('Try again'), [
            'type' => 'button',
            'class' => 'btn btn-ghost btn-sm mt-2',
            '@click' => '$server.sync()',
            'x-show' => '$server.error && $server.error.recovery === "retry"',
            ':disabled' => '$server.busy',
        ]) ?>
        <?= $this->Form->button(__('Reload page'), [
            'type' => 'button',
            'class' => 'btn btn-ghost btn-sm mt-2',
            '@click' => 'window.location.reload()',
            'x-show' => '$server.error && $server.error.recovery === "reload"',
        ]) ?>
    </div>
</div>
