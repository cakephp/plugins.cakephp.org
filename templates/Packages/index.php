<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Package> $featuredPackages
 * @var array<\App\Model\Entity\Package>|\Cake\Collection\CollectionInterface<\App\Model\Entity\Package> $packages
 * @var iterable<\Tags\Model\Entity\Tag> $cakephpTags
 * @var iterable<\Tags\Model\Entity\Tag> $phpTags
 */
?>
<?php if ($featuredPackages) : ?>
    <?php $this->start('above_content'); ?>
    <section class="py-8 sm:py-10">
        <div class="mb-5 flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-cake-red"><?= __('Featured plugins') ?></p>
                <p class="mt-2 text-sm text-base-content/60"><?= __('Curated packages highlighted by the CakePHP community.') ?></p>
            </div>
            <div class="hidden items-center gap-3 md:flex">
                <button type="button" class="btn btn-circle btn-soft btn-primary" data-featured-packages-prev aria-label="<?= __('Previous featured package') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </button>
                <button type="button" class="btn btn-circle btn-soft btn-primary" data-featured-packages-next aria-label="<?= __('Next featured package') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="featured-packages-slider-shell">
            <div class="featured-packages-slider swiper" data-featured-packages-slider>
                <div class="swiper-wrapper">
                    <?php foreach ($featuredPackages as $package) : ?>
                        <div class="swiper-slide">
                            <?= $this->element('Packages/package-tile', ['package' => $package, 'isFeatured' => true]) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mt-4 flex justify-center" data-featured-packages-pagination></div>
        </div>
    </section>
    <?php $this->end(); ?>
<?php endif; ?>

<div
    id="packages-index-content"
    class="packages index content"
    hx-boost="true"
    hx-target="this"
    hx-select="#packages-index-content"
    hx-swap="outerHTML"
    hx-push-url="true"
>
    <template>
        <div id="above-content-slot" hx-swap-oob="true">
            <?php if ($featuredPackages) : ?>
                <div class="w-full border-b overflow-hidden border-cake-red/20 bg-cake-red/5">
                    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                        <?= $this->fetch('above_content') ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </template>

    <?php $featuredPackageNames = array_column((array)$featuredPackages, 'package'); ?>
    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-5 xl:flex-row xl:justify-between xl:items-end">
            <div>
                <h3 class="mt-2 flex items-center gap-3 text-3xl font-semibold text-base-content">
                    <?= __('Packages') ?>
                    <span class="badge badge-soft badge-primary"><?= $this->Paginator->counter('{{count}}') ?></span>
                </h3>
            </div>
            <?php
            // Create form that preserves existing query params via valueSources
            echo $this->Form->create(null, [
                'type' => 'get',
                'valueSources' => 'query',
                'class' => 'join join-horizontal flex-wrap gap-2'
            ]);
            // Preserve search query when applying filters
            echo $this->Form->hidden('search');
            echo $this->Form->control('cakephp_slugs', [
                'label' => false,
                'options' => $cakephpTags,
                'multiple' => true,
                'data-placeholder' => __('CakePHP Version'),
                'class' => 'select select-bordered join-item'
            ]);
            echo $this->Form->control('php_slugs', [
                'label' => false,
                'options' => $phpTags,
                'multiple' => true,
                'data-placeholder' => __('PHP Version'),
                'data-is-php-filter' => true,
                'class' => 'select select-bordered join-item'
            ]);
            echo $this->Form->button(__('Apply'), [
                'type' => 'submit',
            ]);
            echo $this->Form->end();
            ?>
            <div class="flex flex-wrap items-center gap-4 text-sm">
                <p class="font-medium opacity-60"><?= __('Sort by:') ?></p>
                <div class="join">
                    <?php
                    $this->Paginator->setTemplates([
                        'sort' => '<a href="{{url}}" class="join-item btn btn-sm">{{text}}</a>',
                        'sortAsc' => '<a href="{{url}}" class="join-item btn btn-sm btn-primary gap-2" aria-sort="ascending">{{text}}<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m18 15-6-6-6 6"/></svg></a>',
                        'sortDesc' => '<a href="{{url}}" class="join-item btn btn-sm btn-primary gap-2" aria-sort="descending">{{text}}<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></a>',
                    ]);
                    ?>
                    <?= $this->Paginator->sort('downloads', 'Downloads', ['direction' => 'desc']) ?>
                    <?= $this->Paginator->sort('stars', 'Stars', ['direction' => 'desc']) ?>
                    <?= $this->Paginator->sort('latest_stable_release_date', 'Latest Release', ['direction' => 'desc']) ?>
                </div>
            </div>
        </div>

        <section aria-labelledby="all-packages-title">
            <!-- <div class="mb-6 flex items-center justify-between gap-4">
                <h3 id="all-packages-title" class="text-xl font-semibold text-slate-900"><?= __('All packages') ?></h3>
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400"><?= __('Complete index') ?></span>
            </div> -->
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($packages as $package) : ?>
                    <?php if (in_array($package->package, $featuredPackageNames, true)) : ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <?= $this->element('Packages/package-tile', [
                        'package' => $package,
                        'isFeatured' => false,
                    ]) ?>
                <?php endforeach; ?>
            </div>
        </section>

    </div>
    <div class="flex justify-center px-4 py-8 sm:px-6 lg:px-8">
        <?php
        $this->Paginator->setTemplates([
            'number' => '<a href="{{url}}" class="join-item btn btn-sm sm:btn-md">{{text}}</a>',
            'current' => '<button type="button" class="join-item btn btn-sm btn-active sm:btn-md" aria-current="page">{{text}}</button>',
            'ellipsis' => '<span class="join-item btn btn-sm btn-disabled sm:btn-md">{{text}}</span>',
            'first' => '<a href="{{url}}" class="join-item btn btn-sm sm:btn-md">{{text}}</a>',
            'last' => '<a href="{{url}}" class="join-item btn btn-sm sm:btn-md">{{text}}</a>',
            'prevActive' => '<a rel="prev" href="{{url}}" class="join-item btn btn-sm sm:btn-md">{{text}}</a>',
            'prevDisabled' => '<span class="join-item btn btn-sm btn-disabled sm:btn-md">{{text}}</span>',
            'nextActive' => '<a rel="next" href="{{url}}" class="join-item btn btn-sm sm:btn-md">{{text}}</a>',
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
