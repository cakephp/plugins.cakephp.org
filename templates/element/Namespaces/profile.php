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
        <div class="avatar avatar-placeholder shrink-0" x-data="{ failed: false }" aria-hidden="true">
            <div class="namespace-avatar">
                <span><?= h(mb_strtoupper(mb_substr($namespace, 0, 2))) ?></span>
                <?php if ($loading) : ?>
                    <div class="skeleton absolute inset-0 rounded-[inherit] motion-reduce:animate-none" x-show="!$server.error"></div>
                <?php elseif ($profile['avatarUrl'] ?? null) : ?>
                    <?= $this->Html->image($profile['avatarUrl'], [
                        'alt' => '',
                        'width' => 96,
                        'height' => 96,
                        'decoding' => 'async',
                        'referrerpolicy' => 'no-referrer',
                        'x-show' => '!failed',
                        '@error' => 'failed = true',
                        'x-init' => 'failed = $el.complete && $el.naturalWidth === 0',
                        'data-namespace-avatar' => '',
                    ]) ?>
                <?php endif; ?>
            </div>
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
    </nav>
</section>
