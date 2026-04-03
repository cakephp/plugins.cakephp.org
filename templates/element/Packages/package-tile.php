<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Package $package
 * @var bool $isFeatured
 */
$isFeatured = $isFeatured ?? false;
$query = $this->getRequest()->getQueryParams();
?>
<article class="group flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
    <a class="block border-b border-slate-200 bg-slate-50 px-5 py-4 transition group-hover:bg-white"
       target="_blank" rel="noopener noreferrer" href="<?= h($package->repo_url) ?>">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="truncate text-lg font-semibold text-slate-950 transition group-hover:text-cake-red">
                        <?= h($package->package) ?>
                    </h2>
                    <?php if ($isFeatured) : ?>
                        <span class="shrink-0 rounded-full bg-cake-red px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-white">
                            <?= __('Featured') ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <span class="shrink-0 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600">
                <?= __('Plugin') ?>
            </span>
        </div>
    </a>

    <div class="flex flex-1 flex-col p-5">
        <p class="min-h-18 flex-1 text-sm leading-6 text-slate-700 line-clamp-4">
            <?= h($package->description ?: __('No description available.')) ?>
        </p>

        <?php if ($package->cake_php_tags): ?>
            <div class="mt-5 border-t border-dashed border-slate-200 pt-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                    <?= __('CakePHP Compatibility') ?>
                </p>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($package->cake_php_tags as $tag): ?>
                        <?php
                        $tagQuery = $query;
                        $tagQuery['cakephp_slugs'] = array_values(array_unique(array_filter([
                            ...((array)($query['cakephp_slugs'] ?? [])),
                            $tag->slug,
                        ])));
                        unset($tagQuery['page']);
                        ?>
                        <a href="<?= h($this->Url->build(['?' => $tagQuery])) ?>"
                           class="rounded-full bg-cake-red/10 px-3 py-1 text-xs font-medium text-cake-red transition hover:bg-cake-red hover:text-white">
                            <?= h(str_replace('CakePHP: ', '', $tag->label)) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <dl class="mt-5 grid gap-3 border-t border-slate-200 pt-4 text-sm text-slate-600 sm:grid-cols-3">
            <div class="rounded-2xl bg-slate-50 px-3 py-3">
                <dt class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500 [&_svg]:size-4">
                    <?php include WWW_ROOT . 'img' . DS . 'download.svg'; ?>
                    <?= __('Downloads') ?>
                </dt>
                <dd class="mt-2 text-base font-semibold text-slate-950">
                    <?= $this->Number->format($package->downloads) ?>
                </dd>
            </div>
            <div class="rounded-2xl bg-slate-50 px-3 py-3">
                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <?= __('Latest Version') ?>
                </dt>
                <dd class="mt-2 text-base font-semibold text-slate-950">
                    <?= h($package->latest_stable_version ?: __('Unknown')) ?>
                </dd>
            </div>
            <div class="rounded-2xl bg-slate-50 px-3 py-3">
                <dt class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500 [&_svg]:size-4">
                    <?php include WWW_ROOT . 'img' . DS . 'star.svg'; ?>
                    <?= __('Stars') ?>
                </dt>
                <dd class="mt-2 text-base font-semibold text-slate-950">
                    <?= $this->Number->format($package->stars) ?>
                </dd>
            </div>
        </dl>
    </div>
</article>
