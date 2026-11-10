<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var \App\Model\Entity\Package $package
 */
$summaries = [];
foreach (['CakePHP' => $package->cake_php_tag_groups, 'PHP' => $package->php_tag_groups] as $label => $groups) {
    if ($groups) {
        $majors = array_map(static fn(int $major): string => $major . '.x', array_keys($groups));
        $summaries[] = $label . ' ' . implode(', ', $majors);
    }
}
$detailsUrl = $this->Url->build(['_name' => 'plugin:view', 'id' => $package->id]);
?>
<article class="package-row" id="package-<?= (int)$package->id ?>" data-package-row>
    <div class="min-w-0">
        <h3 class="package-name">
            <a href="<?= $detailsUrl ?>" data-open-plugin data-plugin-id="<?= (int)$package->id ?>" data-plugin-name="<?= h($package->package) ?>">
                <?= h($package->package) ?>
            </a>
        </h3>
        <p class="line-clamp-2 text-[0.9375rem] wrap-anywhere"><?= h($package->description ?: __('No description available.')) ?></p>
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
    <div class="package-summary">
        <dl class="package-meta">
            <div>
                <dt><?= __('Downloads') ?></dt>
                <dd><?= $this->Number->format($package->downloads) ?></dd>
            </div>
        </dl>
        <a class="inline-flex min-h-8 items-center gap-1.5 text-[0.8125rem] font-[550]" href="<?= $detailsUrl ?>" aria-label="<?= __('View {0} details', h($package->package)) ?>"
           data-open-plugin data-plugin-id="<?= (int)$package->id ?>" data-plugin-name="<?= h($package->package) ?>">
            <?= __('View details') ?> <span aria-hidden="true">→</span>
        </a>
    </div>
</article>
