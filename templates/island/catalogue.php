<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var bool $hasFilters
 * @var string $search
 * @var \Cake\Datasource\Paging\PaginatedInterface<int, \App\Model\Entity\Package> $packages
 * @var array<string, string> $cakephpTags
 * @var array<string, string> $phpTags
 * @var array<string, mixed> $catalogueQuery
 * @var list<string> $cakephpSlugs
 * @var list<string> $phpSlugs
 * @var array<string, array{name: string, count: int}> $categoryOptions
 * @var string $category
 * @var bool $featuredOnly
 * @var bool $awesomeOnly
 * @var string|null $namespace
 * @var array<string, mixed> $catalogueRoute
 */
$sortLabels = [
    'latest_stable_release_date' => __('Latest release'),
    'downloads' => __('Downloads'),
    'monthly_downloads' => __('Monthly downloads'),
    'stars' => __('Stars'),
];
$sortLabel = $sortLabels[$catalogueQuery['sort']] ?? __('Latest release');
$clearUrl = $this->Url->build($catalogueRoute + [
    '?' => [
        'sort' => $catalogueQuery['sort'],
        'direction' => $catalogueQuery['direction'],
    ],
], [
    'escape' => false,
]);
$clearCategoryUrl = $this->Url->build($catalogueRoute + [
    '?' => array_replace($catalogueQuery, ['category' => '']),
], [
    'escape' => false,
]);
?>
<div class="directory pt-4 md:pt-5 site-width" x-data="catalogue" @click="activate($event)" @keydown.escape="closeMenu($event)"
     @catalogue-version.window="selectVersion($event)" @catalogue-category.window="selectCategory($event)">
    <div class="catalogue-discovery">
        <div class="mb-4 flex items-center gap-3 md:gap-4">
            <?= $this->Form->create(null, [
                'type' => 'get',
                'url' => $catalogueRoute,
                'class' => 'min-w-0 flex-1',
                'id' => 'catalogue-search-form',
                'role' => 'search',
                'aria-label' => __('Find plugins'),
                'x-interact:submit' => '$server.sync()',
            ]) ?>
                <label for="package-search" class="sr-only"><?= __('Search plugins') ?></label>
                <div class="search-control input" id="catalogue-search-control">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m16 16 4.5 4.5" stroke-linecap="round"></path>
                    </svg>
                    <input type="search" name="search" id="package-search" value="<?= h($search) ?>"
                           x-interact:model.live="search" maxlength="100" autocomplete="off"
                           placeholder="<?= __('Search by name or description') ?>" aria-controls="catalogue-results"
                           :aria-busy="!$server.error && ($server.dirty || $server.pending()) ? 'true' : 'false'">
                    <span class="flex shrink-0 items-center gap-2 text-sm leading-[1.6] text-muted" role="status" aria-label="<?= __('Updating results') ?>"
                          x-show="!$server.error && ($server.dirty || $server.pending())" x-cloak>
                        <span class="loading loading-spinner loading-sm" aria-hidden="true"></span>
                        <span class="hidden md:inline"><?= __('Updating results…') ?></span>
                    </span>
                </div>
                <?php foreach (['cakephp_slugs' => $cakephpSlugs, 'php_slugs' => $phpSlugs] as $key => $slugs) : ?>
                    <?php foreach ($slugs as $slug) : ?>
                        <?= $this->Form->hidden($key . '[]', ['value' => $slug]) ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <?= $this->Form->hidden('sort', ['value' => $catalogueQuery['sort']]) ?>
                <?= $this->Form->hidden('direction', ['value' => $catalogueQuery['direction']]) ?>
                <?php if ($category !== '') : ?>
                    <?= $this->Form->hidden('category', ['value' => $category]) ?>
                <?php endif; ?>
                <?php if ($featuredOnly) : ?>
                    <?= $this->Form->hidden('featured', ['value' => '1']) ?>
                <?php endif; ?>
                <?php if ($awesomeOnly) : ?>
                    <?= $this->Form->hidden('awesome', ['value' => '1']) ?>
                <?php endif; ?>
            <?= $this->Form->end() ?>
            <?php if ($hasFilters) : ?>
                <a class="reset-filters" href="<?= h($clearUrl) ?>" data-clear-catalogue
                   aria-label="<?= __('Reset filters') ?>" title="<?= __('Reset filters') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path d="M3 10a9 9 0 1 1 2.6 8.4M3 4v6h6" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span><?= __('Reset filters') ?></span>
                </a>
            <?php endif; ?>
        </div>

        <div class="catalogue-filters">
            <?php
            $filters = [
                [
                    'name' => 'cakephp_slugs',
                    'property' => 'cakephpSlugs',
                    'label' => __('CakePHP version'),
                    'prefix' => 'CakePHP',
                    'options' => $cakephpTags,
                    'selected' => $cakephpSlugs,
                ],
                [
                    'name' => 'php_slugs',
                    'property' => 'phpSlugs',
                    'label' => __('PHP version'),
                    'prefix' => 'PHP',
                    'options' => $phpTags,
                    'selected' => $phpSlugs,
                ],
            ];
            ?>
            <?php foreach ($filters as $filter) : ?>
                <?php
                $selectedLabels = array_values(array_intersect_key($filter['options'], array_flip($filter['selected'])));
                $selectionLabel = $filter['label'];
                if (count($selectedLabels) === 1) {
                    $selectionLabel = str_replace(': ', ' ', $selectedLabels[0]);
                } elseif (count($selectedLabels) > 1) {
                    $selectionLabel = __('{0} ({1})', $filter['prefix'], count($selectedLabels));
                }
                $clearVersionQuery = $catalogueQuery;
                $clearVersionQuery[$filter['name']] = [];
                ?>
                <div class="version-filter" role="group" aria-label="<?= h($filter['label']) ?>">
                    <details class="version-dropdown dropdown" id="versions-<?= h($filter['name']) ?>" name="catalogue-filters"
                             x-data="{ expanded: false }" :open="expanded" @toggle="expanded = $el.open" @click.outside="expanded = false">
                        <summary class="catalogue-filter-toggle btn btn-outline" data-filter-active="<?= $selectedLabels !== [] ? 'true' : 'false' ?>"
                                 aria-label="<?= h(__('{0}: {1}', $filter['label'], $selectionLabel)) ?>" title="<?= h($selectionLabel) ?>">
                            <span class="min-w-0 flex-1 truncate text-left" data-version-selection><?= h($selectionLabel) ?></span>
                            <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="m4 6 4 4 4-4" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </summary>
                        <div class="version-dropdown-panel catalogue-filter-panel dropdown-content">
                            <div class="mb-3 flex min-h-6 items-center justify-between gap-3">
                                <span class="text-sm font-semibold"><?= h($filter['label']) ?></span>
                                <?php if ($selectedLabels !== []) : ?>
                                    <a class="btn btn-ghost btn-xs" aria-label="<?= h(__('Clear {0}', $filter['label'])) ?>"
                                       href="<?= $this->Url->build($catalogueRoute + ['?' => $clearVersionQuery]) ?>"
                                       @click.prevent="clearVersions('<?= h($filter['property']) ?>', $event)"><?= __('Clear') ?></a>
                                <?php endif; ?>
                            </div>
                            <div class="version-options">
                                <?= $this->element('Packages/version_pills', compact('filter', 'catalogueQuery', 'catalogueRoute')) ?>
                            </div>
                        </div>
                    </details>
                </div>
            <?php endforeach; ?>

            <?= $this->element('Packages/category_browser', compact('categoryOptions', 'category', 'catalogueQuery', 'clearCategoryUrl', 'catalogueRoute')) ?>
        </div>
    </div>

    <section class="catalogue-panel" id="packages-index-content" aria-label="<?= __('Plugin directory') ?>">
        <div class="catalogue-toolbar">
            <div class="catalogue-summary">
                <h2 aria-live="polite"><span class="result-count badge badge-soft badge-primary"><?= $this->Paginator->counter('{{count}}') ?></span> <?= __('plugins') ?></h2>
                <?php if ($category !== '') : ?>
                    <a class="selected-category badge badge-outline" href="<?= h($clearCategoryUrl) ?>" data-category-slug=""
                       aria-label="<?= h(__('Remove category filter: {0}', $categoryOptions[$category]['name'])) ?>">
                        <?= h($categoryOptions[$category]['name']) ?>
                        <span aria-hidden="true">×</span>
                    </a>
                <?php endif; ?>
            </div>
            <div class="catalogue-controls">
                <?php $featuredQuery = array_replace($catalogueQuery, ['featured' => $featuredOnly ? '0' : '1']); ?>
                <a class="curation-toggle community-picks-toggle btn btn-outline" role="button" aria-pressed="<?= $featuredOnly ? 'true' : 'false' ?>"
                   href="<?= $this->Url->build($catalogueRoute + ['?' => $featuredQuery]) ?>"
                   data-toggle-featured @keydown.space.prevent="$el.click()">
                    <?= $this->element('Packages/community_pick_icon') ?>
                    <?= __('Community picks') ?>
                </a>
                <?php $awesomeQuery = array_replace($catalogueQuery, ['awesome' => $awesomeOnly ? '0' : '1']); ?>
                <a class="curation-toggle btn btn-outline" role="button" aria-pressed="<?= $awesomeOnly ? 'true' : 'false' ?>"
                   href="<?= $this->Url->build($catalogueRoute + ['?' => $awesomeQuery]) ?>"
                   data-toggle-awesome @keydown.space.prevent="$el.click()">
                    <?= $this->element('awesome_logo') ?>
                    <?= __('Awesome only') ?>
                </a>
                <div class="sort-control">
                    <span class="text-muted"><?= __('Sort by') ?></span>
                    <details class="sort-menu dropdown dropdown-end">
                        <summary aria-label="<?= __('Sort plugins') ?>">
                            <span class="truncate"><?= h($sortLabel) ?></span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="m4 6 4 4 4-4" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </summary>
                        <div class="sort-options dropdown-content">
                            <?= $this->Paginator->sort('latest_stable_release_date', __('Latest release'), ['direction' => 'desc']) ?>
                            <?= $this->Paginator->sort('downloads', __('Downloads'), ['direction' => 'desc']) ?>
                            <?= $this->Paginator->sort('monthly_downloads', __('Monthly downloads'), ['direction' => 'desc']) ?>
                            <?= $this->Paginator->sort('stars', __('Stars'), ['direction' => 'desc']) ?>
                        </div>
                    </details>
                </div>
            </div>
        </div>

        <div class="catalogue-error" role="alert" x-show="$server.error" x-cloak>
            <p><?= __('We couldn’t update the results. Your current list is still here. Please try again.') ?></p>
            <button class="action-link btn btn-outline" type="button" @click="$server.sync()" :disabled="$server.pending()">
                <?= __('Retry') ?>
            </button>
        </div>

        <div id="catalogue-results" :aria-busy="!$server.error && ($server.dirty || $server.pending()) ? 'true' : 'false'">
            <?php if (count($packages) === 0) : ?>
                <div class="empty-results">
                    <h3><?= __('No plugins found') ?></h3>
                    <p><?= __('Try another search or reset your filters.') ?></p>
                    <a class="action-link btn btn-outline" href="<?= h($clearUrl) ?>" data-clear-catalogue><?= __('Clear all') ?></a>
                </div>
            <?php else : ?>
                <?php foreach ($packages as $package) : ?>
                    <?= $this->element('Packages/package-row', [
                        'package' => $package,
                        'namespace' => $namespace,
                        'sort' => $catalogueQuery['sort'],
                    ]) ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (count($packages) > 0) : ?>
            <div class="catalogue-pagination">
                <p><?= $this->Paginator->counter(__('Showing {{start}}–{{end}} of {{count}}')) ?></p>
                <nav class="flex flex-wrap items-center gap-1.5" aria-label="<?= __('Results pages') ?>">
                    <?php if ($this->Paginator->hasPrev()) : ?>
                        <?= $this->Paginator->first(__('First')) ?>
                    <?php else : ?>
                        <span class="page-link btn btn-outline" aria-disabled="true"><?= __('First') ?></span>
                    <?php endif; ?>
                    <?= $this->Paginator->prev(__('Previous')) ?>
                    <?= $this->Paginator->numbers(['modulus' => 2]) ?>
                    <?= $this->Paginator->next(__('Next')) ?>
                    <?php if ($this->Paginator->hasNext()) : ?>
                        <?= $this->Paginator->last(__('Last')) ?>
                    <?php else : ?>
                        <span class="page-link btn btn-outline" aria-disabled="true"><?= __('Last') ?></span>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    </section>
</div>
