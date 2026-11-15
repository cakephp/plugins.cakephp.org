<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var array{plugins: int, publishers: int, monthly_downloads: int|null, current_support: array{major: string, plugins: int}|null} $stats
 */
$statLabels = [
    'plugins' => __('Plugins'),
    'publishers' => __('Publishers'),
    'monthly_downloads' => __('Monthly downloads'),
];
?>
<section class="min-w-0" aria-label="<?= __('Ecosystem snapshot') ?>" data-ecosystem-snapshot>
    <h2 class="sr-only"><?= __('Ecosystem snapshot') ?></h2>
    <p class="sr-only"><?= __('Across all listed CakePHP plugins.') ?></p>
    <dl class="grid grid-cols-2 gap-x-6 gap-y-5 <?= $stats['current_support'] !== null ? 'md:grid-cols-4' : 'md:grid-cols-3' ?>">
        <?php foreach ($statLabels as $key => $label) : ?>
            <?php $value = $stats[$key]; ?>
            <div class="flex min-w-0 flex-col gap-1.5">
                <dt class="order-1 text-xs text-muted md:text-sm"><?= h($label) ?></dt>
                <dd class="text-2xl font-semibold tracking-tight tabular-nums md:text-3xl" data-ecosystem-stat="<?= h($key) ?>"
                    title="<?= h($value !== null ? $this->Number->format($value) : __('Available after the next package sync')) ?>">
                    <?= h($value !== null ? $this->Number->compact($value) : '—') ?>
                </dd>
            </div>
        <?php endforeach; ?>
        <?php if ($stats['current_support'] !== null) : ?>
            <div class="flex min-w-0 flex-col gap-1.5">
                <dt class="order-1 text-xs text-muted md:text-sm"><?= h(__('CakePHP {0} plugins', $stats['current_support']['major'])) ?></dt>
                <dd class="text-2xl font-semibold tracking-tight text-primary tabular-nums md:text-3xl" data-ecosystem-stat="current_support"
                    title="<?= h(__('Plugins with an indexed release compatible with CakePHP {0}', $stats['current_support']['major'])) ?>">
                    <?= h($this->Number->compact($stats['current_support']['plugins'])) ?>
                </dd>
            </div>
        <?php endif; ?>
    </dl>
</section>
