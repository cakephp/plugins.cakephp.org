<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var bool $showFeatured
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
 * @var bool $awesomeOnly
 */
$sortLabels = [
    'latest_stable_release_date' => __('Latest release'),
    'downloads' => __('Downloads'),
    'stars' => __('Stars'),
];
$sortLabel = $sortLabels[$catalogueQuery['sort']] ?? __('Latest release');
$clearUrl = $this->Url->build([
    'controller' => 'Packages', 'action' => 'index', 'plugin' => null,
    '?' => ['sort' => $catalogueQuery['sort'], 'direction' => $catalogueQuery['direction']],
]);
$clearCategoryUrl = $this->Url->build([
    'controller' => 'Packages', 'action' => 'index', 'plugin' => null,
    '?' => array_replace($catalogueQuery, ['category' => '']),
]);
?>
<div class="directory site-width" x-data="catalogue" @click="activate($event)" @keydown.escape="closeMenu($event)"
     @catalogue-version.window="selectVersion($event)" @catalogue-category.window="selectCategory($event)">
    <h1 class="sr-only"><?= __('CakePHP plugins') ?></h1>

    <div class="catalogue-discovery">
        <div class="catalogue-search-row">
            <?= $this->Form->create(null, [
                'type' => 'get',
                'url' => ['controller' => 'Packages', 'action' => 'index', 'plugin' => null],
                'class' => 'catalogue-search',
                'id' => 'catalogue-search-form',
                'role' => 'search',
                'aria-label' => __('Find plugins'),
                'x-interact:submit' => '$server.sync()',
            ]) ?>
                <label for="package-search" class="sr-only"><?= __('Search plugins') ?></label>
                <div class="search-control" id="catalogue-search-control">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m16 16 4.5 4.5" stroke-linecap="round"></path>
                    </svg>
                    <input type="search" name="search" id="package-search" value="<?= h($search) ?>"
                           x-interact:model.live="search" maxlength="100" autocomplete="off"
                           placeholder="<?= __('Search by name or description') ?>" aria-controls="catalogue-results"
                           :aria-busy="!$server.error && ($server.dirty || $server.pending()) ? 'true' : 'false'">
                    <span class="search-status" role="status" aria-label="<?= __('Updating results') ?>"
                          x-show="!$server.error && ($server.dirty || $server.pending())" x-cloak>
                        <span class="loading loading-spinner loading-sm" aria-hidden="true"></span>
                        <span class="search-status-text"><?= __('Updating results…') ?></span>
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
                ['name' => 'cakephp_slugs', 'label' => __('CakePHP version'), 'prefix' => 'CakePHP', 'options' => $cakephpTags, 'selected' => $cakephpSlugs],
                ['name' => 'php_slugs', 'label' => __('PHP version'), 'prefix' => 'PHP', 'options' => $phpTags, 'selected' => $phpSlugs],
            ];
            ?>
            <?php foreach ($filters as $filter) : ?>
                <div class="version-filter" role="group" aria-label="<?= h($filter['label']) ?>">
                    <span class="filter-label"><?= h($filter['label']) ?></span>
                    <div class="version-options" tabindex="0" aria-label="<?= __('{0} choices; scroll for older versions', $filter['label']) ?>">
                        <?php foreach ($filter['options'] as $slug => $label) : ?>
                            <?php
                            $version = str_replace($filter['prefix'] . ': ', '', $label);
                            $selected = in_array($slug, $filter['selected'], true);
                            $filterQuery = $catalogueQuery;
                            $filterQuery[$filter['name']] = $selected
                                ? array_values(array_diff($filter['selected'], [$slug]))
                                : [...$filter['selected'], $slug];
                            ?>
                            <a class="version-pill" role="button" aria-pressed="<?= $selected ? 'true' : 'false' ?>"
                               aria-label="<?= h($filter['prefix'] . ' ' . $version) ?>"
                               href="<?= $this->Url->build(['controller' => 'Packages', 'action' => 'index', 'plugin' => null, '?' => $filterQuery]) ?>"
                               @keydown.space.prevent="$el.click()"
                               data-version-group="<?= h($filter['name']) ?>" data-version-slug="<?= h($slug) ?>">
                                <?= h($version) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?= $this->element('Packages/category_browser', compact('categoryOptions', 'category', 'catalogueQuery', 'clearCategoryUrl')) ?>
    </div>

    <?php if ($showFeatured) : ?>
        <?= $this->Interact->render('FeaturedPlugins', key: 'featured-plugins') ?>
    <?php endif; ?>

    <section class="catalogue-panel" id="packages-index-content" aria-label="<?= __('Plugin directory') ?>">
        <div class="catalogue-toolbar">
            <div class="catalogue-summary">
                <h2 aria-live="polite"><span class="result-count"><?= $this->Paginator->counter('{{count}}') ?></span> <?= __('plugins') ?></h2>
                <?php if ($category !== '') : ?>
                    <a class="selected-category" href="<?= h($clearCategoryUrl) ?>" data-category-slug=""
                       aria-label="<?= h(__('Remove category filter: {0}', $categoryOptions[$category]['name'])) ?>">
                        <?= h($categoryOptions[$category]['name']) ?>
                        <span aria-hidden="true">×</span>
                    </a>
                <?php endif; ?>
            </div>
            <div class="catalogue-controls">
                <?php $awesomeQuery = array_replace($catalogueQuery, ['awesome' => $awesomeOnly ? '0' : '1']); ?>
                <a class="curation-toggle" role="button" aria-pressed="<?= $awesomeOnly ? 'true' : 'false' ?>"
                   href="<?= $this->Url->build(['plugin' => null, 'controller' => 'Packages', 'action' => 'index', '?' => $awesomeQuery]) ?>"
                   data-toggle-awesome @keydown.space.prevent="$el.click()">
                    <?= $this->element('awesome_logo') ?>
                    <?= __('Awesome only') ?>
                </a>
                <div class="sort-control">
                    <span class="muted"><?= __('Sort by') ?></span>
                    <details class="sort-menu">
                        <summary aria-label="<?= __('Sort plugins') ?>">
                            <?= h($sortLabel) ?>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="m4 6 4 4 4-4" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </summary>
                        <div class="sort-options">
                            <?= $this->Paginator->sort('latest_stable_release_date', __('Latest release'), ['direction' => 'desc']) ?>
                            <?= $this->Paginator->sort('downloads', __('Downloads'), ['direction' => 'desc']) ?>
                            <?= $this->Paginator->sort('stars', __('Stars'), ['direction' => 'desc']) ?>
                        </div>
                    </details>
                </div>
            </div>
        </div>

        <div class="catalogue-error" role="alert" x-show="$server.error" x-cloak>
            <p><?= __('We couldn’t update the results. Your current list is still here. Please try again.') ?></p>
            <button class="action-link" type="button" @click="$server.sync()" :disabled="$server.pending()">
                <?= __('Retry') ?>
            </button>
        </div>

        <div id="catalogue-results" :aria-busy="!$server.error && ($server.dirty || $server.pending()) ? 'true' : 'false'">
            <?php if (count($packages) === 0) : ?>
                <div class="empty-results">
                    <h3><?= __('No plugins found') ?></h3>
                    <p><?= __('Try another search or reset your filters.') ?></p>
                    <a class="action-link" href="<?= h($clearUrl) ?>" data-clear-catalogue><?= __('Clear all') ?></a>
                </div>
            <?php else : ?>
                <?php foreach ($packages as $package) : ?>
                    <?= $this->element('Packages/package-row', compact('package')) ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (count($packages) > 0) : ?>
            <div class="catalogue-pagination">
                <p><?= $this->Paginator->counter(__('Showing {{start}}–{{end}} of {{count}}')) ?></p>
                <nav class="page-links" aria-label="<?= __('Results pages') ?>">
                    <?php if ($this->Paginator->hasPrev()) : ?>
                        <?= $this->Paginator->first(__('First')) ?>
                    <?php else : ?>
                        <span class="page-link" aria-disabled="true"><?= __('First') ?></span>
                    <?php endif; ?>
                    <?= $this->Paginator->prev(__('Previous')) ?>
                    <?= $this->Paginator->numbers(['modulus' => 2]) ?>
                    <?= $this->Paginator->next(__('Next')) ?>
                    <?php if ($this->Paginator->hasNext()) : ?>
                        <?= $this->Paginator->last(__('Last')) ?>
                    <?php else : ?>
                        <span class="page-link" aria-disabled="true"><?= __('Last') ?></span>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    </section>
</div>
