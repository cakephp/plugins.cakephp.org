<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var string|null $namespace
 */
$route = $namespace !== null ? [
    '_name' => 'plugin:namespace',
    'namespace' => $namespace,
] : [
    '_name' => 'plugin:index',
];
$query = $this->getRequest()->getQueryParams();
$reloadUrl = $this->Url->build($route + ['?' => $query]);
$hasFilters = array_any(['search', 'cakephp_slugs', 'php_slugs', 'category', 'awesome'], static fn(string $key): bool => !empty($query[$key]));
$showFeatured = $namespace === null && !$hasFilters && ($query['page'] ?? '1') === '1';
?>
<div class="directory pt-4 md:pt-5 site-width" data-catalogue-loading>
    <div data-catalogue-skeleton x-show="!$server.error">
        <span class="sr-only" role="status"><?= __('Loading plugins…') ?></span>
        <div aria-hidden="true" class="[&_.skeleton]:motion-reduce:animate-none">
            <div class="catalogue-discovery">
                <div class="skeleton mb-4 h-14 w-full rounded-lg"></div>
                <div class="catalogue-filters">
                    <div class="skeleton h-11 rounded-lg md:h-10"></div>
                    <div class="skeleton h-11 rounded-lg md:h-10"></div>
                    <div class="skeleton h-11 rounded-lg min-[360px]:col-span-2 md:col-span-1 md:h-10"></div>
                </div>
            </div>
            <?php if ($showFeatured) : ?>
                <div class="featured-section">
                    <div class="skeleton mb-3 h-6 w-40"></div>
                    <div class="featured-grid">
                        <?php for ($i = 0; $i < 3; $i++) : ?>
                            <div class="featured-item p-4">
                                <div class="flex items-start gap-3">
                                    <div class="skeleton size-12 shrink-0 rounded-xl"></div>
                                    <div class="min-w-0 flex-1 space-y-2">
                                        <div class="skeleton h-3 w-20"></div>
                                        <div class="skeleton h-5 w-4/5"></div>
                                        <div class="skeleton h-4 w-28"></div>
                                    </div>
                                </div>
                                <div class="mt-5 space-y-2">
                                    <div class="skeleton h-4 w-full"></div>
                                    <div class="skeleton h-4 w-3/4"></div>
                                </div>
                                <div class="mt-5 flex justify-between border-t border-base-300 pt-3">
                                    <div class="skeleton h-3 w-28"></div>
                                    <div class="skeleton h-4 w-10"></div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="catalogue-panel">
                <div class="catalogue-toolbar">
                    <div class="skeleton h-6 w-28"></div>
                    <div class="flex gap-2">
                        <div class="skeleton h-10 w-24 md:w-32"></div>
                        <div class="skeleton h-10 w-24 md:w-32"></div>
                    </div>
                </div>
                <?php for ($i = 0; $i < 4; $i++) : ?>
                    <div class="grid gap-4 border-b border-base-300 p-4 last:border-0 md:grid-cols-[minmax(0,1fr)_8rem] md:p-5">
                        <div class="space-y-3">
                            <div class="skeleton h-5 w-3/4 max-w-96"></div>
                            <div class="skeleton h-4 w-full max-w-xl"></div>
                            <div class="skeleton h-3 w-36"></div>
                            <div class="flex gap-2"><div class="skeleton h-5 w-28"></div><div class="skeleton h-5 w-16"></div></div>
                            <div class="skeleton h-3 w-40"></div>
                        </div>
                        <div class="flex items-end justify-between gap-3 md:flex-col md:items-end">
                            <div class="skeleton h-7 w-20"></div>
                            <div class="skeleton h-4 w-24"></div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <div class="catalogue-panel p-5" role="alert" x-show="$server.error" x-cloak>
        <p class="mb-4 text-sm text-muted"><?= __('We couldn’t load the plugins. Please try again.') ?></p>
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" class="action-link btn btn-outline" @click="$server.sync()" :disabled="$server.busy"
                    x-show="$server.error && $server.error.recovery === 'retry'"><?= __('Retry') ?></button>
            <a class="action-link btn btn-outline" href="<?= $reloadUrl ?>"
               x-show="$server.error && $server.error.recovery === 'reload'"><?= __('Reload page') ?></a>
        </div>
    </div>
    <noscript>
        <div class="catalogue-panel p-5">
            <p class="text-sm text-muted"><?= __('Enable JavaScript to search and browse plugins.') ?></p>
        </div>
    </noscript>
</div>
