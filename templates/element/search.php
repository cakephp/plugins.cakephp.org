<?php
/**
 * Global search element with autocomplete dropdown.
 *
 * @var \App\View\AppView $this
 */
$request = $this->getRequest();
$isPackagesIndex = $request->getParam('controller') === 'Packages' && $request->getParam('action') === 'index';
$searchValue = (string)$request->getQuery('search', '');
$cakephpSlugs = (array)$request->getQuery('cakephp_slugs', []);
$phpSlugs = (array)$request->getQuery('php_slugs', []);

$searchFormOptions = [
    'type' => 'get',
    'url' => ['controller' => 'Packages', 'action' => 'index'],
    'class' => 'w-full max-w-xl relative',
    'valueSources' => 'query',
];
if ($isPackagesIndex) {
    $searchFormOptions += [
        'hx-get' => $this->Url->build(['controller' => 'Packages', 'action' => 'index']),
        'hx-target' => '#packages-index-content',
        'hx-select' => '#packages-index-content',
        'hx-swap' => 'outerHTML',
        'hx-push-url' => 'true',
    ];
}
echo $this->Form->create(null, $searchFormOptions);

foreach ($cakephpSlugs as $slug) {
    echo $this->Form->hidden('cakephp_slugs[]', ['value' => $slug]);
}
foreach ($phpSlugs as $slug) {
    echo $this->Form->hidden('php_slugs[]', ['value' => $slug]);
}
?>
<div class="w-full" x-data="packageSearch" @click.outside="close()" @keydown="onKeydown($event)">
    <label class="input w-full bg-white text-base-content">
        <svg class="h-4 w-4 shrink-0 opacity-60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
            <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.3-4.3"></path>
            </g>
        </svg>
        <input type="search" name="search" x-ref="input" x-model="query"
               value="<?= h($searchValue) ?>"
               placeholder="<?= __('Search packages...') ?>"
               class="grow min-w-0"
               autocomplete="off"
               role="combobox"
               :aria-expanded="open"
               aria-haspopup="listbox"
               aria-controls="autocomplete-listbox"
               @focus="query.length >= 2 && results.length > 0 && (open = true)" />
        <span class="loading loading-spinner loading-xs" x-show="loading" x-cloak></span>
    </label>

    <!-- Autocomplete dropdown -->
    <div x-show="open" x-cloak x-transition.opacity.duration.150ms
         class="fixed inset-x-0 top-[calc(var(--navbar-height,4rem)+0.25rem)] z-50 mx-2 overflow-hidden rounded-2xl border border-base-300 bg-base-100 shadow-2xl sm:absolute sm:inset-x-auto sm:top-full sm:left-0 sm:right-0 sm:mx-0 sm:mt-1">
        <ul x-ref="listbox" id="autocomplete-listbox" role="listbox" class="max-h-[70vh] sm:max-h-[28rem] overflow-y-auto divide-y divide-base-200">
            <template x-for="(result, index) in results" :key="result.package">
                <li role="option"
                    :aria-selected="index === selectedIndex"
                    :class="index === selectedIndex ? 'bg-base-200' : ''"
                    class="autocomplete-item cursor-pointer transition-colors hover:bg-base-200"
                    @click="selectResult(result)"
                    @mouseenter="selectedIndex = index">
                    <div class="flex items-start gap-3 px-4 py-3">
                        <!-- Package icon -->
                        <div class="hidden sm:block flex-none mt-0.5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-cake-red/10 text-cake-red">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m20 7-8-4-8 4m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-sm truncate" x-text="result.package"></span>
                                <span x-show="result.latest_version"
                                      class="badge badge-xs badge-ghost font-mono"
                                      x-text="result.latest_version"></span>
                            </div>

                            <p class="mt-0.5 text-xs text-base-content/60 line-clamp-1" x-text="result.description || 'No description available.'"></p>

                            <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1">
                                <!-- Stats -->
                                <div class="flex items-center gap-3 text-[11px] text-base-content/50">
                                    <span class="inline-flex items-center gap-1" x-show="result.downloads">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1m-4-4-4 4m0 0-4-4m4 4V4" />
                                        </svg>
                                        <span x-text="formatNumber(result.downloads)"></span>
                                    </span>
                                    <span class="inline-flex items-center gap-1" x-show="result.stars">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.786-.196-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L2.98 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69l1.616-4.673z" />
                                        </svg>
                                        <span x-text="formatNumber(result.stars)"></span>
                                    </span>
                                </div>

                                <!-- Version badges (hidden on mobile) -->
                                <div class="hidden sm:flex items-center gap-1" x-show="result.cakephp_versions.length">
                                    <template x-for="v in result.cakephp_versions" :key="'cake-'+v">
                                        <span class="badge badge-xs badge-primary badge-soft font-mono" x-text="'CakePHP ' + v"></span>
                                    </template>
                                </div>
                                <div class="hidden sm:flex items-center gap-1" x-show="result.php_versions.length">
                                    <template x-for="v in result.php_versions" :key="'php-'+v">
                                        <span class="badge badge-xs badge-secondary badge-soft font-mono" x-text="'PHP ' + v"></span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Arrow -->
                        <div class="flex-none mt-1 text-base-content/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </div>
                    </div>
                </li>
            </template>
        </ul>

        <!-- Footer -->
        <div class="border-t border-base-200 bg-base-200/50 px-4 py-2.5 flex items-center justify-between">
            <span class="hidden sm:inline text-[11px] text-base-content/40">
                <kbd class="kbd kbd-xs">↑</kbd> <kbd class="kbd kbd-xs">↓</kbd> <?= __('navigate') ?>
                · <kbd class="kbd kbd-xs">Enter</kbd> <?= __('open') ?>
                · <kbd class="kbd kbd-xs">Esc</kbd> <?= __('close') ?>
            </span>
            <button type="submit"
                    class="text-xs font-medium text-cake-red hover:underline sm:ml-auto"
                    @click.prevent="close(); $nextTick(() => $refs.input.form?.requestSubmit())">
                <?= __('View all results →') ?>
            </button>
        </div>
    </div>
</div>
<?= $this->Form->end() ?>
