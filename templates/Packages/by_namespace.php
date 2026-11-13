<?php
/**
 * @var \App\View\AppView $this
 * @var string $namespace
 * @var array{plugins: int, downloads: int, stars: int} $stats
 * @var string|null $githubUrl
 */
$this->assign('title', __('Plugins by {0}', $namespace));
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
            <?php if ($githubUrl !== null) : ?>
                <a class="btn btn-outline btn-sm self-start md:self-center [&_svg]:size-4" href="<?= h($githubUrl) ?>" target="_blank" rel="noopener noreferrer">
                    <?= $this->element('github_icon') ?>
                    <?= __('GitHub profile') ?> <span aria-hidden="true">↗</span>
                </a>
            <?php endif; ?>
        </div>
    </section>
    <section class="mt-4" aria-label="<?= __('Namespace statistics') ?>" data-namespace-stats>
        <dl class="stats stats-horizontal grid w-full grid-cols-[1fr_2fr_1fr] rounded-2xl border border-base-300 bg-base-100 shadow-none md:grid-cols-3">
            <?php foreach ($statLabels as $key => $label) : ?>
                <div class="stat min-w-0 px-2 py-4 md:px-6 md:py-5">
                    <dt class="stat-title text-xs"><?= h($label) ?></dt>
                    <dd class="stat-value text-base tabular-nums md:text-2xl" data-namespace-stat="<?= h($key) ?>"><?= $this->Number->format($stats[$key]) ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    </section>
</div>
<?= $this->Interact->render('Catalogue', props: compact('namespace'), key: 'namespace-catalogue-' . $namespace) ?>
<?= $this->element('Packages/plugin-modal') ?>
