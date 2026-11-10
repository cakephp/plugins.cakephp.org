<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var bool $showFeatured
 * @var \Cake\Datasource\Paging\PaginatedInterface<int, \App\Model\Entity\Package> $packages
 * @var array<string, string> $cakephpTags
 * @var array<string, string> $phpTags
 * @var array<string, mixed> $catalogueQuery
 * @var list<string> $cakephpSlugs
 * @var list<string> $phpSlugs
 */
?>
<div x-data="catalogue" @click="toggleVersion($event)">
    <div class="main-loading-overlay" x-show="$server.loading()" x-cloak role="status">
        <div class="main-loading-indicator-card flex items-center gap-3 rounded-2xl border border-base-300 bg-base-100/95 px-4 py-3 shadow-2xl">
            <span class="loading loading-ring loading-xl text-cake-red" aria-hidden="true"></span>
            <span class="text-sm font-semibold"><?= __('Loading content...') ?></span>
        </div>
    </div>
    <div class="container mx-auto px-4" x-show="$server.error" x-cloak>
        <p role="alert" x-text="$server.error ? $server.error.message : ''"></p>
        <button type="button" class="btn btn-sm" x-show="$server.error && $server.error.recovery === 'retry'" @click="$server.sync()"><?= __('Try again') ?></button>
        <a class="link" href="<?= $this->Url->build(['controller' => 'Packages', 'action' => 'index', 'plugin' => null]) ?>"><?= __('Reload catalogue') ?></a>
    </div>
    <?php if ($showFeatured) : ?>
        <?= $this->Interact->render('FeaturedPlugins', key: 'featured-plugins') ?>
    <?php endif; ?>

    <div id="packages-index-content" class="packages index content container mx-auto">
        <div class="px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-col gap-5">
                <div>
                    <h3 class="mt-2 flex items-center gap-3 text-3xl font-semibold text-base-content">
                        <?= __('Packages') ?>
                        <span class="badge badge-soft badge-primary"><?= $this->Paginator->counter('{{count}}') ?></span>
                    </h3>
                </div>
                <div class="space-y-2">
                    <?php
                    $filters = [
                        [
                            'name' => 'cakephp_slugs', 'legend' => __('CakePHP version'), 'prefix' => 'CakePHP',
                            'options' => $cakephpTags, 'selected' => $cakephpSlugs,
                        ],
                        [
                            'name' => 'php_slugs', 'legend' => __('PHP version'), 'prefix' => 'PHP',
                            'options' => $phpTags, 'selected' => $phpSlugs,
                        ],
                    ];
                    ?>
                    <?php foreach ($filters as $filter) : ?>
                        <div role="group" aria-label="<?= h($filter['legend']) ?>" class="flex items-start gap-3">
                            <span class="w-28 shrink-0 py-1 text-xs font-medium text-base-content/60"><?= h($filter['legend']) ?></span>
                            <div class="flex min-w-0 flex-wrap gap-1">
                                <?php foreach ($filter['options'] as $slug => $label) : ?>
                                    <?php
                                    $version = str_replace($filter['prefix'] . ': ', '', $label);
                                    $selected = in_array($slug, $filter['selected'], true);
                                    $filterQuery = $catalogueQuery;
                                    $filterQuery[$filter['name']] = $selected
                                        ? array_values(array_diff($filter['selected'], [$slug]))
                                        : [...$filter['selected'], $slug];
                                    ?>
                                    <a role="button" aria-pressed="<?= $selected ? 'true' : 'false' ?>"
                                       aria-label="<?= h($filter['prefix'] . ' ' . $version) ?>"
                                       href="<?= $this->Url->build(['controller' => 'Packages', 'action' => 'index', 'plugin' => null, '?' => $filterQuery]) ?>"
                                       @keydown.space.prevent="$el.click()"
                                       data-version-group="<?= h($filter['name']) ?>" data-version-slug="<?= h($slug) ?>"
                                       class="inline-flex min-h-7 items-center justify-center rounded-full border border-base-300 bg-base-100 px-2 py-0.5
                                              text-xs font-medium text-base-content/70 transition hover:border-primary/50 hover:text-primary
                                              focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary
                                              aria-pressed:border-primary aria-pressed:bg-primary aria-pressed:text-primary-content">
                                        <?= h($version) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="flex flex-wrap items-center gap-4 text-sm">
                    <p class="font-medium opacity-60"><?= __('Sort by:') ?></p>
                    <div class="join">
                        <?php
                        $this->Paginator->setTemplates([
                            'sort' => '<a rel="nofollow" href="{{url}}" class="join-item btn btn-sm">{{text}}</a>',
                            'sortAsc' => '<a rel="nofollow" href="{{url}}" class="join-item btn btn-sm btn-primary gap-2" aria-sort="ascending">{{text}}<svg '
                                . 'xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
                                . 'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m18 15-6-6-6 6"/></svg></a>',
                            'sortDesc' => '<a rel="nofollow" href="{{url}}" class="join-item btn btn-sm btn-primary gap-2" aria-sort="descending">{{text}}<svg '
                                . 'xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
                                . 'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></a>',
                        ]);
                        ?>
                        <?= $this->Paginator->sort('downloads', 'Downloads', ['direction' => 'desc']) ?>
                        <?= $this->Paginator->sort('stars', 'Stars', ['direction' => 'desc']) ?>
                        <?= $this->Paginator->sort('latest_stable_release_date', 'Latest Release', ['direction' => 'desc']) ?>
                    </div>
                </div>
            </div>

            <section aria-label="<?= __('All packages') ?>">
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <?php foreach ($packages as $package) : ?>
                        <?= $this->element('Packages/package-tile', [
                            'package' => $package,
                            'isFeatured' => false,
                            'catalogueQuery' => $catalogueQuery,
                        ]) ?>
                    <?php endforeach; ?>
                </div>
            </section>

        </div>
        <div class="flex justify-center px-4 py-8 sm:px-6 lg:px-8">
            <?php
            $this->Paginator->setTemplates([
                'number' => '<a rel="nofollow" href="{{url}}" class="join-item btn btn-sm sm:btn-md">{{text}}</a>',
                'current' => '<button type="button" class="join-item btn btn-sm btn-active sm:btn-md" aria-current="page">{{text}}</button>',
                'ellipsis' => '<span class="join-item btn btn-sm btn-disabled sm:btn-md">{{text}}</span>',
                'first' => '<a rel="nofollow" href="{{url}}" class="join-item btn btn-sm sm:btn-md">{{text}}</a>',
                'last' => '<a rel="nofollow" href="{{url}}" class="join-item btn btn-sm sm:btn-md">{{text}}</a>',
                'prevActive' => '<a rel="prev nofollow" href="{{url}}" class="join-item btn btn-sm sm:btn-md">{{text}}</a>',
                'prevDisabled' => '<span class="join-item btn btn-sm btn-disabled sm:btn-md">{{text}}</span>',
                'nextActive' => '<a rel="next nofollow" href="{{url}}" class="join-item btn btn-sm sm:btn-md">{{text}}</a>',
                'nextDisabled' => '<span class="join-item btn btn-sm btn-disabled sm:btn-md">{{text}}</span>',
            ]);
            ?>
            <div class="w-full max-w-max">
                <div class="join flex-wrap justify-center sm:hidden">
                    <?= $this->Paginator->prev('‹ ' . __('previous')) ?>
                    <span class="join-item btn btn-sm btn-disabled pointer-events-none">
                        <?= $this->Paginator->counter('{{page}} / {{pages}}') ?>
                    </span>
                    <?= $this->Paginator->next(__('next') . ' ›') ?>
                </div>
                <div class="hidden sm:flex sm:justify-center">
                    <div class="join">
                        <?= $this->Paginator->first('« ' . __('first')) ?>
                        <?= $this->Paginator->prev('‹ ' . __('previous')) ?>
                        <?= $this->Paginator->numbers(['modulus' => 4]) ?>
                        <?= $this->Paginator->next(__('next') . ' ›') ?>
                        <?= $this->Paginator->last(__('last') . ' »') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
