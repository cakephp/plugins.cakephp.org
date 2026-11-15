<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var \App\Model\Entity\Package $package
 * @var string|null $namespace
 * @var string $sort
 */
[$statLabel, $statValue] = match ($sort) {
    'monthly_downloads' => [__('Monthly downloads'), $package->monthly_downloads],
    'stars' => [__('Stars'), $package->stars],
    default => [__('Downloads'), $package->downloads],
};
$pick = $package->community_pick;
$summaries = [];
foreach (['CakePHP' => $package->cake_php_tag_groups, 'PHP' => $package->php_tag_groups] as $label => $groups) {
    if ($groups) {
        $majors = array_map(static fn(int $major): string => $major . '.x', array_keys($groups));
        $summaries[] = $label . ' ' . implode(', ', $majors);
    }
}
[$packageNamespace, $packageName] = explode('/', $package->package, 2);
$detailsUrl = $this->Url->build([
    '_name' => 'plugin:view',
    'namespace' => $packageNamespace,
    'name' => $packageName,
]);
?>
<article class="package-row<?= $pick !== null ? ' community-pick-row' : '' ?>"<?= $pick !== null ? ' data-community-pick' : '' ?> id="package-<?= (int)$package->id ?>" data-package-row>
    <div class="package-identity">
        <?php if ($pick !== null) : ?>
            <?= $this->element('Packages/plugin-avatar', ['packageName' => $package->package, 'logo' => $pick->logo]) ?>
        <?php endif; ?>
        <div class="min-w-0">
            <?php if ($pick !== null) : ?>
                <span class="community-pick-badge">
                    <?= $this->element('Packages/community_pick_icon') ?>
                    <?= __('Community pick') ?>
                </span>
            <?php endif; ?>
            <h3 class="package-name">
                <a href="<?= $detailsUrl ?>" data-open-plugin data-plugin-id="<?= (int)$package->id ?>" data-plugin-name="<?= h($package->package) ?>">
                    <?= h($packageNamespace) ?>/<wbr><?= h($packageName) ?>
                </a>
            </h3>
            <?php if ($pick?->title !== null) : ?>
                <p class="community-pick-title"><?= h($pick->title) ?></p>
            <?php endif; ?>
            <p class="line-clamp-2 text-[0.9375rem] wrap-anywhere"><?= h($pick->description ?? ($package->description ?: __('No description available.'))) ?></p>
            <?php if ($namespace !== $packageNamespace) : ?>
                <?= $this->Html->link(__('More by {0}', $packageNamespace), [
                    '_name' => 'plugin:namespace',
                    'namespace' => $packageNamespace,
                ], [
                    'class' => 'mt-1 inline-flex min-h-6 items-center text-xs text-muted hover:text-primary',
                    'x-interact:navigate' => '',
                ]) ?>
            <?php endif; ?>
            <?php if ($package->categories) : ?>
                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                    <?= $this->element('Packages/awesome_badge', compact('package')) ?>
                    <?php foreach (array_slice($package->categories, 0, 2) as $category) : ?>
                        <span class="category-label badge badge-outline"><?= h($category->name) ?></span>
                    <?php endforeach; ?>
                    <?php if (count($package->categories) > 2) : ?>
                        <span class="category-label badge badge-outline">+<?= count($package->categories) - 2 ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($summaries !== []) : ?>
                <p class="mt-2 text-[0.8125rem] text-muted"><?= h(implode(' · ', $summaries)) ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="package-summary">
        <dl class="package-meta">
            <div>
                <dt><?= h($statLabel) ?></dt>
                <dd title="<?= $statValue !== null ? h($this->Number->format($statValue)) : __('Awaiting package sync') ?>"><?= $statValue !== null ? $this->Number->format($statValue) : '—' ?></dd>
            </div>
        </dl>
        <a class="inline-flex min-h-8 items-center gap-1.5 text-[0.8125rem] font-[550]" href="<?= $detailsUrl ?>" aria-label="<?= __('View {0} details', h($package->package)) ?>"
           data-open-plugin data-plugin-id="<?= (int)$package->id ?>" data-plugin-name="<?= h($package->package) ?>">
            <?= __('View details') ?> <span aria-hidden="true">→</span>
        </a>
    </div>
</article>
