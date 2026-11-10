<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var list<\App\Model\Entity\Package> $featuredPackages
 * @var array<string, mixed> $catalogueQuery
 */
?>
<div>
    <?php if ($featuredPackages) : ?>
        <div class="w-full border-b overflow-hidden border-cake-red/20 bg-cake-red/5">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <section class="py-8 sm:py-10" x-data="featuredPackages">
                    <div x-interact:ignore>
                        <div class="mb-5 flex items-end justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium uppercase tracking-[0.2em] text-cake-red"><?= __('Featured plugins') ?></p>
                                <p class="mt-2 text-sm text-base-content/60"><?= __('Curated packages highlighted by the CakePHP community.') ?></p>
                            </div>
                            <div class="hidden items-center gap-3 md:flex">
                                <button type="button" class="btn btn-circle btn-soft btn-primary" data-featured-packages-prev
                                        aria-label="<?= __('Previous featured package') ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="m15 18-6-6 6-6" />
                                    </svg>
                                </button>
                                <button type="button" class="btn btn-circle btn-soft btn-primary" data-featured-packages-next
                                        aria-label="<?= __('Next featured package') ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
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
                                            <?= $this->element('Packages/package-tile', [
                                                'package' => $package,
                                                'isFeatured' => true,
                                                'catalogueQuery' => $catalogueQuery,
                                            ]) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-center" data-featured-packages-pagination></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    <?php endif; ?>
</div>
