<?php
/**
 * @var \App\View\AppView $this
 * @var string $namespace
 * @var array{plugins: int, downloads: int, stars: int} $stats
 * @var string|null $githubUrl
 * @var array|string $backUrl
 */
$this->assign('title', __('Plugins by {0}', $namespace));
$statLabels = [
    'plugins' => __('Plugins'),
    'downloads' => __('Downloads'),
    'stars' => __('Stars'),
];
?>
<header class="namespace-hero" data-namespace-banner>
    <div class="site-width relative">
        <div class="namespace-toolbar">
            <?= $this->Html->link('← ' . __('Back'), $backUrl, [
                'class' => 'inline-flex min-h-8 items-center text-sm text-muted',
                'x-interact:navigate' => '',
            ]) ?>
        </div>
        <div class="namespace-profile">
            <?php if ($githubUrl !== null) : ?>
                <?= $this->Interact->render(
                    'NamespaceProfile',
                    props: compact('namespace', 'githubUrl'),
                    key: 'namespace-profile-' . $namespace,
                    loading: 'lazy',
                    placeholder: 'Loading/namespace_profile',
                ) ?>
            <?php else : ?>
                <?= $this->element('Namespaces/profile', [
                    'namespace' => $namespace,
                    'githubUrl' => null,
                    'profile' => null,
                    'loading' => false,
                ]) ?>
            <?php endif; ?>
            <section class="namespace-statistics" aria-label="<?= __('Namespace statistics') ?>" data-namespace-stats x-data="namespaceCounters">
                <dl class="grid grid-cols-3 gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)_minmax(0,1fr)] md:gap-10">
                    <?php foreach ($statLabels as $key => $label) : ?>
                        <div class="flex min-w-0 flex-col gap-2">
                            <dt class="order-1 text-xs text-muted md:text-sm"><?= h($label) ?></dt>
                            <dd class="namespace-stat-value"
                                title="<?= h($this->Number->format($stats[$key])) ?>" aria-label="<?= h($this->Number->format($stats[$key])) ?>"
                                data-count-up-value="<?= h($stats[$key]) ?>" data-namespace-stat="<?= h($key) ?>"><?= h($this->Number->compact($stats[$key])) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </section>
        </div>
    </div>
</header>
<?= $this->Interact->render('Catalogue', props: compact('namespace'), key: 'namespace-catalogue-' . $namespace) ?>
<?= $this->element('Packages/plugin-modal') ?>
