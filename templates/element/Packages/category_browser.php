<?php
use App\Service\AwesomeListImporter;
use App\Utility\FilterSelection;

/**
 * @var \App\View\IslandTemplateView $this
 * @var array<string, array{name: string, count: int}> $categoryOptions
 * @var string $category
 * @var list<string> $excludedCategories
 * @var array<string, mixed> $catalogueQuery
 * @var string $clearCategoryUrl
 * @var array<string, mixed> $catalogueRoute
 */
$allCategoriesSelected = $category === '' && $excludedCategories === [];
$categoryLabel = $category === '' ? __('Category') : $categoryOptions[$category]['name'];
if ($excludedCategories !== []) {
    $categoryLabel = count($excludedCategories) === 1 && $category === ''
        ? __('Not {0}', $categoryOptions[$excludedCategories[0]]['name'])
        : __('{0} ({1} excluded)', $categoryLabel, count($excludedCategories));
}
?>
<details class="category-browser dropdown" id="catalogue-categories" name="catalogue-filters" x-ref="categoryBrowser"
         @toggle="categoriesToggled($event)" @click.outside="dismissCategories">
    <summary class="catalogue-filter-toggle btn btn-outline" data-filter-active="<?= !$allCategoriesSelected ? 'true' : 'false' ?>"
             aria-label="<?= h(__('Browse categories: {0}', $categoryLabel)) ?>" title="<?= h($categoryLabel) ?>">
        <span class="min-w-0 flex-1 truncate text-left"><?= h($categoryLabel) ?></span>
        <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="m4 6 4 4 4-4" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
    </summary>
    <div class="category-browser-panel catalogue-filter-panel dropdown-content">
        <div class="mb-3 flex shrink-0 flex-wrap items-center justify-between gap-3">
            <span class="text-sm font-semibold"><?= __('Categories') ?></span>
            <label class="category-search" x-cloak>
                <span class="sr-only"><?= __('Find a category') ?></span>
                <input class="input input-sm" type="search" x-model="categorySearch" placeholder="<?= __('Find a category') ?>"
                       autocomplete="off" aria-controls="category-grid">
            </label>
        </div>
        <div class="category-choices grid grid-cols-2 gap-2 md:grid-cols-3" id="category-grid" x-ref="categoryGrid" role="group" aria-label="<?= __('Plugin categories') ?>">
            <a class="category-choice" href="<?= h($clearCategoryUrl) ?>" data-category-slug="" data-category-name="<?= __('All categories') ?>"
               x-show="matchesCategory($el.dataset.categoryName)" <?= $allCategoriesSelected ? 'aria-current="true"' : '' ?>>
                <span class="wrap-anywhere"><?= __('All categories') ?></span>
                <span aria-hidden="true">→</span>
            </a>
            <?php foreach ($categoryOptions as $slug => $option) : ?>
                <?php
                $categorySelected = $slug === $category;
                $excluded = in_array($slug, $excludedCategories, true);
                $state = $excluded ? 'exclude' : ($categorySelected ? 'include' : 'neutral');
                $description = $excluded ? __('Excluded. Click to clear.') : ($categorySelected ? __('Included. Click to exclude.') : __('Click to include.'));
                $selection = FilterSelection::cycle($category !== '' ? [$category] : [], $excludedCategories, $slug, multiple: false);
                $categoryQuery = array_replace($catalogueQuery, ['category' => $selection['included'][0] ?? '', 'exclude_categories' => $selection['excluded']]);
                ?>
                <a class="category-choice" href="<?= $this->Url->build($catalogueRoute + ['?' => $categoryQuery]) ?>"
                   data-category-slug="<?= h($slug) ?>" data-category-name="<?= h($option['name']) ?>"
                   data-category-cycle data-filter-state="<?= $state ?>" aria-description="<?= h($description) ?>"
                   aria-label="<?= h(__n('{0}, {1} plugin', '{0}, {1} plugins', $option['count'], $option['name'], $option['count'])) ?>"
                   title="<?= __('Total plugins in this category') ?>" x-show="matchesCategory($el.dataset.categoryName)" <?= $categorySelected ? 'aria-current="true"' : '' ?>>
                    <span class="wrap-anywhere"><?= h($option['name']) ?></span>
                    <?php if ($excluded) : ?>
                        <span class="filter-excluded-marker" aria-hidden="true">−</span>
                    <?php endif; ?>
                    <span class="shrink-0 text-xs leading-normal text-muted tabular-nums" aria-hidden="true"><?= $this->Number->format($option['count']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <p class="mt-4 text-sm leading-[1.6] text-muted" role="status" x-show="categorySearch && !hasCategoryMatches()" x-cloak><?= __('No matching categories. Try another name.') ?></p>
        <p class="mt-3 shrink-0 text-xs leading-[1.6] text-muted"><?= __('Click to include, again to exclude, again to clear.') ?></p>
        <p class="mt-2 shrink-0 text-xs leading-[1.6] text-muted">
            <?= __('Categories from') ?>
            <a href="<?= AwesomeListImporter::SOURCE_URL ?>" target="_blank" rel="noopener noreferrer">Awesome CakePHP</a>.
        </p>
    </div>
</details>
