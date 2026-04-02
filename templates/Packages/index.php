<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Package> $featuredPackages
 * @var iterable<\App\Model\Entity\Package> $packages
 * @var iterable<\Tags\Model\Entity\Tag> $cakephpTags
 * @var iterable<\Tags\Model\Entity\Tag> $phpTags
 */
?>
<div class="packages index content">
    <?php $featuredPackageNames = array_column((array)$featuredPackages, 'package'); ?>
    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-cake-red"><?= __('Directory') ?></p>
                <h3 class="mt-2 text-3xl font-semibold text-slate-950"><?= __('Packages') ?></h3>
            </div>
            <?php
            echo $this->Form->create(null, ['valueSources' => 'query', 'class' => 'flex flex-wrap items-center gap-4 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm']);
            echo $this->Form->control('search', ['label' => false, 'placeholder' => __('Search...')]);
            echo $this->Form->control('cakephp_slugs', [
                'label' => false,
                'options' => $cakephpTags,
                'empty' => __('No Filter'),
                'multiple' => true,
                'data-placeholder' => __('CakePHP Version'),
            ]);
//            echo $this->Form->control('php_slugs', [
//                'label' => false,
//                'options' => $phpTags,
//                'empty' => __('No Filter'),
//                'multiple' => true,
//                'data-placeholder' => __('PHP Version'),
//                'data-is-php-filter' => true,
//            ]);
            echo $this->Form->button('Search', ['type' => 'submit']);
            echo $this->Form->end();
            ?>
            <div class="flex flex-wrap items-center gap-4 text-sm text-slate-600">
                <p class="font-medium"><?= __('Sort by:') ?></p>
                <div class="flex gap-4">
                    <?= $this->Paginator->sort('downloads') ?>
                    <?= $this->Paginator->sort('stars') ?>
                </div>
            </div>
        </div>

        <?php if ($featuredPackages) : ?>
            <section class="mb-8">
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-[0.2em] text-cake-red"><?= __('Featured') ?></p>
                    </div>
                    <div class="hidden items-center gap-3 md:flex">
                        <button type="button" class="featured-packages-slider-button" data-featured-packages-prev aria-label="<?= __('Previous featured package') ?>">
                            &larr;
                        </button>
                        <button type="button" class="featured-packages-slider-button" data-featured-packages-next aria-label="<?= __('Next featured package') ?>">
                            &rarr;
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
                </div>
            </section>
        <?php endif; ?>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($packages as $package) : ?>
                <?= $this->element('Packages/package-tile', [
                    'package' => $package,
                    'isFeatured' => in_array($package->package, $featuredPackageNames, true),
                ]) ?>
            <?php endforeach; ?>
        </div>

    </div>
    <div class="flex flex-wrap justify-between items-center gap-3 px-4 py-8 sm:px-6 lg:px-8">
        <p class="text-sm text-slate-500"><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>

        <ul class="flex flex-wrap items-center gap-3">
            <?= $this->Paginator->first('« ' . __('first')) ?>
            <?= $this->Paginator->prev('‹ ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' ›') ?>
            <?= $this->Paginator->last(__('last') . ' »') ?>
        </ul>
    </div>
</div>
