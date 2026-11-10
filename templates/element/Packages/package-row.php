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
    <div class="package-info">
        <h3 class="package-name">
            <a href="<?= $detailsUrl ?>" data-open-plugin data-plugin-id="<?= (int)$package->id ?>" data-plugin-name="<?= h($package->package) ?>">
                <?= h($package->package) ?>
            </a>
        </h3>
        <p class="package-description"><?= h($package->description ?: __('No description available.')) ?></p>
        <?php if ($summaries !== []) : ?>
            <p class="package-compatibility"><?= h(implode(' · ', $summaries)) ?></p>
        <?php endif; ?>
    </div>
    <div class="package-summary">
        <dl class="package-meta">
            <div>
                <dt><?= __('Downloads') ?></dt>
                <dd><?= $this->Number->format($package->downloads) ?></dd>
            </div>
        </dl>
        <a class="package-details-link" href="<?= $detailsUrl ?>" aria-label="<?= __('View {0} details', h($package->package)) ?>"
           data-open-plugin data-plugin-id="<?= (int)$package->id ?>" data-plugin-name="<?= h($package->package) ?>">
            <?= __('View details') ?> <span aria-hidden="true">→</span>
        </a>
    </div>
</article>
