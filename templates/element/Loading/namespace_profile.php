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
</div>
