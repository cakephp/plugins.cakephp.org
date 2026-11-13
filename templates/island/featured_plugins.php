<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var list<\App\Model\Entity\Package> $featuredPackages
 * @var array<string, array{logo: string|null, title: string|null, description: string|null}> $featuredMetadata
 */
?>
<div>
    <?php if ($featuredPackages !== []) : ?>
        <section class="featured-section" aria-label="<?= __('Featured plugins') ?>" data-featured-plugins>
            <h2>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path d="m12 3 2.8 5.7 6.3.9-4.6 4.5 1.1 6.3-5.6-3-5.6 3 1.1-6.3-4.6-4.5 6.3-.9L12 3Z" stroke-linejoin="round"></path>
                </svg>
                <?= __('Featured plugins') ?>
            </h2>
            <div class="featured-grid">
                <?php foreach ($featuredPackages as $package) : ?>
                    <?php
                    [$vendor, $name] = array_pad(explode('/', $package->package, 2), 2, '');
                    $tone = ['rose', 'violet', 'amber'][hexdec(substr(hash('sha256', $package->package), 0, 4)) % 3];
                    ?>
                    <article class="featured-item group" data-plugin-tone="<?= $tone ?>">
                        <a class="featured-card" href="<?= $this->Url->build(['_name' => 'plugin:view', 'id' => $package->id]) ?>"
                           aria-label="<?= __('View {0} details', h($package->package)) ?>"
                           data-open-plugin data-plugin-id="<?= (int)$package->id ?>" data-plugin-name="<?= h($package->package) ?>">
                            <?= $this->element('Packages/plugin-avatar', [
                                'packageName' => $package->package,
                                'logo' => $featuredMetadata[$package->package]['logo'] ?? null,
                            ]) ?>
                            <div class="min-w-0">
                                <div class="flex items-start justify-between gap-2.5">
                                    <div class="featured-card-name">
                                        <span class="mb-0.5 block text-xs leading-[1.6] text-muted"><?= h($vendor) ?></span>
                                        <h3><?= h($featuredMetadata[$package->package]['title'] ?? ($name ?: $vendor)) ?></h3>
                                        <?= $this->element('Packages/awesome_badge', compact('package')) ?>
                                    </div>
                                    <span class="featured-card-arrow" aria-hidden="true">→</span>
                                </div>
                                <p class="featured-description"><?= h($featuredMetadata[$package->package]['description'] ?? ($package->description ?: __('No description available.'))) ?></p>
                            </div>
                            <div class="featured-card-footer">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                        <path d="M12 3v12m-4-4 4 4 4-4M4 16v5h16v-5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    <?= __('{0} downloads', $this->Number->format($package->downloads)) ?>
                                </span>
                                <?php if ($package->latest_stable_version) : ?>
                                    <span class="featured-release badge badge-outline" title="<?= __('Latest stable release') ?>"><?= h($package->latest_stable_version) ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>
