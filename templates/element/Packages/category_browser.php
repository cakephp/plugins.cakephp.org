<?php
use App\Service\AwesomeListImporter;

/**
 * @var \App\View\IslandTemplateView $this
 * @var array<string, array{name: string, count: int}> $categoryOptions
 * @var string $category
 * @var array<string, mixed> $catalogueQuery
 * @var string $clearCategoryUrl
 */
$allCategoriesSelected = $category === '';
?>
<details class="category-browser" id="catalogue-categories" x-ref="categoryBrowser" @toggle="categoriesToggled($event)">
    <summary>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <rect x="3" y="3" width="5" height="5" rx="1"></rect>
            <rect x="12" y="3" width="5" height="5" rx="1"></rect>
            <rect x="3" y="12" width="5" height="5" rx="1"></rect>
            <rect x="12" y="12" width="5" height="5" rx="1"></rect>
        </svg>
        <?= __('Browse categories') ?>
        <svg class="category-browser-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="m4 6 4 4 4-4" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
    </summary>
    <div class="category-browser-content">
        <div class="category-browser-heading">
            <p class="category-source">
                <?= __('Categories from') ?>
                <a href="<?= AwesomeListImporter::SOURCE_URL ?>" target="_blank" rel="noopener noreferrer">Awesome CakePHP</a>.
            </p>
            <label class="category-search" x-cloak>
                <span class="sr-only"><?= __('Find a category') ?></span>
                <input type="search" x-model="categorySearch" placeholder="<?= __('Find a category') ?>" autocomplete="off" aria-controls="category-grid">
            </label>
        </div>
        <div class="category-grid" id="category-grid" x-ref="categoryGrid" role="group" aria-label="<?= __('Plugin categories') ?>">
            <a class="category-choice" href="<?= h($clearCategoryUrl) ?>" data-category-slug="" data-category-name="<?= __('All categories') ?>"
               x-show="matchesCategory($el.dataset.categoryName)" <?= $allCategoriesSelected ? 'aria-current="true"' : '' ?>>
                <span class="category-choice-label"><?= __('All categories') ?></span>
                <span aria-hidden="true">→</span>
            </a>
            <?php foreach ($categoryOptions as $slug => $option) : ?>
                <?php
                $categoryQuery = array_replace($catalogueQuery, ['category' => $slug]);
                $categorySelected = $slug === $category;
                ?>
                <a class="category-choice" href="<?= $this->Url->build(['plugin' => null, 'controller' => 'Packages', 'action' => 'index', '?' => $categoryQuery]) ?>"
                   data-category-slug="<?= h($slug) ?>" data-category-name="<?= h($option['name']) ?>"
                   aria-label="<?= h(__n('{0}, {1} plugin', '{0}, {1} plugins', $option['count'], $option['name'], $option['count'])) ?>"
                   title="<?= __('Total plugins in this category') ?>" x-show="matchesCategory($el.dataset.categoryName)" <?= $categorySelected ? 'aria-current="true"' : '' ?>>
                    <span class="category-choice-label"><?= h($option['name']) ?></span>
                    <span class="category-choice-count" aria-hidden="true"><?= $this->Number->format($option['count']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <p class="category-no-matches" role="status" x-show="categorySearch && !hasCategoryMatches()" x-cloak><?= __('No matching categories. Try another name.') ?></p>
    </div>
</details>
