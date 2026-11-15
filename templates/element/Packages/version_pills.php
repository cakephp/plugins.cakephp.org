<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var array{name: string, prefix: string, options: array<string, string>, selected: list<string>, excluded: list<string>} $filter
 * @var array<string, mixed> $catalogueQuery
 * @var array<string, mixed> $catalogueRoute
 */
use App\Utility\CakephpVersion;
use App\Utility\FilterSelection;

?>
<?php foreach ($filter['options'] as $slug => $label) : ?>
    <?php
    $version = str_replace($filter['prefix'] . ': ', '', $label);
    $selected = in_array($slug, $filter['selected'], true);
    $excluded = in_array($slug, $filter['excluded'], true);
    $state = $excluded ? 'exclude' : ($selected ? 'include' : 'neutral');
    $description = $excluded ? __('Excluded. Click to clear.') : ($selected ? __('Included. Click to exclude.') : __('Click to include.'));
    $development = CakephpVersion::isDevelopmentSlug($slug);
    $filterQuery = $catalogueQuery;
    $selection = FilterSelection::cycle($filter['selected'], $filter['excluded'], $slug);
    $filterQuery[$filter['name']] = $selection['included'];
    $filterQuery['exclude_' . $filter['name']] = $selection['excluded'];
    ?>
    <a class="version-pill badge badge-outline<?= $development ? ' version-development' : '' ?>" role="button" aria-pressed="<?= $selected || $excluded ? 'true' : 'false' ?>"
       data-filter-state="<?= $state ?>" aria-description="<?= h($description) ?>"
       title="<?= h($development ? __('CakePHP {0} development builds', (int)$version) : $filter['prefix'] . ' ' . $version) ?>"
       aria-label="<?= h($filter['prefix'] . ' ' . $version) ?>"
       href="<?= $this->Url->build($catalogueRoute + ['?' => $filterQuery]) ?>"
       @keydown.space.prevent="$el.click()"
       data-version-group="<?= h($filter['name']) ?>" data-version-slug="<?= h($slug) ?>">
        <?= h($version) ?>
        <?php if ($excluded) : ?>
            <span class="filter-excluded-marker" aria-hidden="true">−</span>
        <?php endif; ?>
    </a>
<?php endforeach; ?>
