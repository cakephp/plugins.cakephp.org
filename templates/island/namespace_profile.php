<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var string $namespace
 * @var string $githubUrl
 * @var array{avatarUrl: string|null, bio: string|null}|null $profile
 */
?>
<div data-namespace-profile>
    <?= $this->element('Namespaces/profile', [
        'namespace' => $namespace,
        'githubUrl' => $githubUrl,
        'profile' => $profile,
        'loading' => false,
    ]) ?>
</div>
