<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var array{name: string, prefix: string, options: array<string, string>, selected: list<string>} $filter
 * @var array<string, mixed> $catalogueQuery
 * @var array<string, mixed> $catalogueRoute
 */
use App\Utility\CakephpVersion;

?>
<?php foreach ($filter['options'] as $slug => $label) : ?>
    <?php
    $version = str_replace($filter['prefix'] . ': ', '', $label);
    $selected = in_array($slug, $filter['selected'], true);
    $development = CakephpVersion::isDevelopmentSlug($slug);
    $filterQuery = $catalogueQuery;
    $filterQuery[$filter['name']] = $selected
        ? array_values(array_diff($filter['selected'], [$slug]))
        : [...$filter['selected'], $slug];
    ?>
    <a class="version-pill badge badge-outline<?= $development ? ' version-development' : '' ?>" role="button" aria-pressed="<?= $selected ? 'true' : 'false' ?>"
       title="<?= h($development ? __('CakePHP {0} development builds', (int)$version) : $filter['prefix'] . ' ' . $version) ?>"
       aria-label="<?= h($filter['prefix'] . ' ' . $version) ?>"
       href="<?= $this->Url->build($catalogueRoute + ['?' => $filterQuery]) ?>"
       @keydown.space.prevent="$el.click()"
       data-version-group="<?= h($filter['name']) ?>" data-version-slug="<?= h($slug) ?>">
        <?= h($version) ?>
    </a>
<?php endforeach; ?>
