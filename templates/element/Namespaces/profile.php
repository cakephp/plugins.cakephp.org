<?php
/**
 * @var \App\View\AppView|\App\View\IslandTemplateView $this
 * @var string $namespace
 * @var string|null $githubUrl
 * @var array{avatarUrl: string|null, bio: string|null}|null $profile
 * @var bool $loading
 */
$packagistUrl = 'https://packagist.org/packages/' . rawurlencode($namespace) . '/';
?>
<section class="min-w-0" aria-labelledby="namespace-title" data-namespace-hero>
    <div class="namespace-identity">
        <div class="avatar avatar-placeholder shrink-0">
            <?= $this->Github->avatar($namespace, $githubUrl, [
                'avatarUrl' => $profile['avatarUrl'] ?? null,
                'loading' => $loading,
                'imageAttributes' => ['data-namespace-avatar' => ''],
            ]) ?>
        </div>
        <div class="min-w-0 flex-1">
            <h1 id="namespace-title" class="namespace-title"><?= h($namespace) ?></h1>
            <p class="namespace-description" data-namespace-description><?= h($profile['bio'] ?? __('Plugins for your CakePHP projects.')) ?></p>
        </div>
    </div>
    <nav class="namespace-profile-links" aria-label="<?= __('Namespace profiles') ?>">
        <?php if ($githubUrl !== null) : ?>
            <a class="namespace-profile-link btn btn-ghost btn-square btn-sm" href="<?= h($githubUrl) ?>" target="_blank" rel="noopener noreferrer"
               aria-label="<?= __('GitHub profile') ?>" title="<?= __('GitHub profile') ?>">
                <?= $this->element('github_icon') ?>
            </a>
        <?php endif; ?>
        <a class="namespace-profile-link btn btn-ghost btn-square btn-sm" href="<?= h($packagistUrl) ?>" target="_blank" rel="noopener noreferrer"
           aria-label="<?= __('Packagist') ?>" title="<?= __('Packagist') ?>">
            <?= $this->Html->image('packagist-logo.png', [
                'alt' => '',
                'width' => 22,
                'height' => 22,
                'class' => 'size-5.5',
            ]) ?>
        </a>
        <?php if ($loading) : ?>
            <span class="sr-only" role="alert" x-show="$server.error" x-cloak><?= __('Profile details are temporarily unavailable.') ?></span>
            <button type="button" class="btn btn-ghost btn-xs ml-1 text-muted hover:text-primary"
                    title="<?= __('Profile details are temporarily unavailable.') ?>"
                    @click="$server.sync()" :disabled="$server.busy"
                    x-show="$server.error && $server.error.recovery === 'retry'" x-cloak><?= __('Retry profile') ?></button>
            <a href="" class="btn btn-ghost btn-xs ml-1 text-muted hover:text-primary"
                    title="<?= __('Reload to refresh the profile request.') ?>"
                    x-show="$server.error && $server.error.recovery === 'reload'" x-cloak><?= __('Reload page') ?></a>
        <?php endif; ?>
    </nav>
</section>
