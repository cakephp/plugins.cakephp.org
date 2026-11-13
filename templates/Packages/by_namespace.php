<?php
/**
 * @var \App\View\AppView $this
 * @var string $namespace
 * @var array{plugins: int, downloads: int, stars: int} $stats
 * @var string|null $githubUrl
 */
$this->assign('title', __('Plugins by {0}', $namespace));
$packagistUrl = 'https://packagist.org/packages/' . rawurlencode($namespace) . '/';
$statLabels = [
    'plugins' => __('Plugins'),
    'downloads' => __('Downloads'),
    'stars' => __('Stars'),
];
?>
<div class="site-width pt-5 md:pt-8">
    <?= $this->Html->link('← ' . __('Browse all plugins'), '/', [
        'class' => 'mb-5 inline-flex min-h-8 items-center text-sm text-muted',
        'x-interact:navigate' => '',
    ]) ?>
    <div class="grid gap-4 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
        <section class="hero rounded-2xl border border-base-300 bg-base-100" aria-labelledby="namespace-title" data-namespace-hero>
            <div class="hero-content w-full max-w-none flex-col items-stretch gap-6 p-5 md:flex-row md:items-center md:justify-between md:p-8">
                <div class="flex min-w-0 items-start gap-4">
                    <div class="avatar avatar-placeholder shrink-0" aria-hidden="true">
                        <div class="size-16 rounded-2xl bg-primary/10 text-2xl font-bold text-primary">
                            <?= h(mb_strtoupper(mb_substr($namespace, 0, 2))) ?>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-muted"><?= __('Composer namespace') ?></p>
                        <h1 id="namespace-title" class="text-2xl leading-tight font-bold wrap-anywhere md:text-3xl"><?= __('Plugins by {0}', h($namespace)) ?></h1>
                        <p class="mt-2 text-sm text-muted"><?= __('Explore CakePHP plugins published under {0}.', h($namespace)) ?></p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 self-start md:self-center">
                    <?php if ($githubUrl !== null) : ?>
                        <a class="btn btn-ghost btn-square btn-sm [&_svg]:size-5" href="<?= h($githubUrl) ?>" target="_blank" rel="noopener noreferrer"
                           aria-label="<?= __('GitHub profile') ?>">
                            <?= $this->element('github_icon') ?>
                        </a>
                    <?php endif; ?>
                    <a class="btn btn-ghost btn-square btn-sm" href="<?= h($packagistUrl) ?>" target="_blank" rel="noopener noreferrer"
                       aria-label="<?= __('Packagist') ?>">
                        <?= $this->Html->image('packagist-logo.png', [
                            'alt' => '',
                            'width' => 24,
                            'height' => 24,
                            'class' => 'size-6',
                        ]) ?>
                    </a>
                </div>
            </div>
        </section>
        <section class="min-w-0" aria-label="<?= __('Namespace statistics') ?>" data-namespace-stats>
            <dl class="stats stats-horizontal grid h-full w-full grid-cols-[1fr_2fr_1fr] rounded-2xl border border-base-300 bg-base-100 shadow-none">
                <?php foreach ($statLabels as $key => $label) : ?>
                    <div class="stat min-w-0 content-center px-2 py-4 sm:px-4 md:py-5 lg:px-3 xl:px-4">
                        <dt class="stat-title text-xs"><?= h($label) ?></dt>
                        <dd class="stat-value text-base tabular-nums md:text-2xl" data-namespace-stat="<?= h($key) ?>"><?= $this->Number->format($stats[$key]) ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </section>
    </div>
</div>
<?= $this->Interact->render('Catalogue', props: compact('namespace'), key: 'namespace-catalogue-' . $namespace) ?>
<?= $this->element('Packages/plugin-modal') ?>
