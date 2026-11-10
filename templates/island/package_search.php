<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var string $query
 * @var iterable<\App\Model\Entity\Package> $suggestions
 */
?>
<div x-data="packageSearch" @click.outside="close()" @keydown="onKeydown($event)" @interact:request-applied.window="refreshSuggestions($event)">
    <?= $this->Form->create(null, [
        'type' => 'get',
        'url' => ['controller' => 'Packages', 'action' => 'index', 'plugin' => null],
        'class' => 'w-full relative',
        'x-interact:submit' => 'submit()',
    ]) ?>
    <?php foreach (['cakephp_slugs', 'php_slugs'] as $key) : ?>
        <?php foreach ((array)$this->getRequest()->getQuery($key, []) as $slug) : ?>
            <?= $this->Form->hidden($key . '[]', ['value' => $slug]) ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <label class="input w-full bg-base-100 text-base-content">
        <svg class="h-4 w-4 shrink-0 opacity-60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
            <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.3-4.3"></path>
            </g>
        </svg>
        <input type="search" name="search" x-ref="input" x-interact:model.live="query"
               value="<?= h($query) ?>" maxlength="100"
               placeholder="<?= __('Search packages...') ?>" aria-label="<?= __('Search packages') ?>"
               class="grow min-w-0" autocomplete="off" role="combobox"
               :aria-expanded="open" :aria-activedescendant="activeId"
               :aria-busy="!$server.error && ($server.dirty || $server.pending()) ? 'true' : 'false'"
               aria-autocomplete="list" aria-haspopup="listbox" aria-controls="autocomplete-listbox"
               @input="onInput()" @focus="showSuggestions()" />
        <span class="loading loading-spinner loading-xs" x-show="!$server.error && ($server.dirty || $server.pending())" x-cloak role="status" aria-label="<?= __('Loading suggestions') ?>"></span>
    </label>
    <div x-show="open" x-cloak x-transition.opacity.duration.150ms
         class="fixed inset-x-0 top-[calc(var(--navbar-height,4rem)+0.25rem)] z-50 mx-2 overflow-hidden rounded-2xl border border-base-300
                bg-base-100 text-base-content shadow-2xl sm:absolute sm:inset-x-auto sm:top-full sm:left-0 sm:right-0 sm:mx-0 sm:mt-1">
        <ul x-ref="listbox" id="autocomplete-listbox" role="listbox" class="max-h-[70vh] sm:max-h-112 overflow-y-auto divide-y divide-base-200">
            <?php foreach ($suggestions as $index => $package) : ?>
                <li id="suggestion-<?= (int)$index ?>" role="option" :aria-selected="selectedIndex === <?= (int)$index ?>"
                    :class="selectedIndex === <?= (int)$index ?> ? 'bg-base-200' : ''" @mouseenter="selectedIndex = <?= (int)$index ?>">
                    <?php $repoUrl = preg_match('#^https://#i', $package->repo_url) ? $package->repo_url : '#'; ?>
                    <a class="autocomplete-item flex items-start gap-3 px-4 py-3 transition-colors hover:bg-base-200"
                       href="<?= h($repoUrl) ?>" target="_blank" rel="noopener noreferrer" tabindex="-1" @click="close()">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-sm truncate"><?= h($package->package) ?></span>
                                <?php if ($package->latest_stable_version) : ?>
                                    <span class="badge badge-xs badge-ghost font-mono"><?= h($package->latest_stable_version) ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="mt-0.5 text-xs text-base-content/60 line-clamp-1"><?= h($package->description ?: __('No description available.')) ?></p>
                            <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-base-content/50">
                                <span><?= __('{0} downloads', $this->Number->format($package->downloads)) ?></span>
                                <span><?= __('{0} stars', $this->Number->format($package->stars)) ?></span>
                                <?php foreach ($package->cake_php_tag_groups as $major => $tags) : ?>
                                    <span class="hidden sm:inline-flex badge badge-xs badge-primary badge-soft font-mono">CakePHP <?= h($major) ?>.x</span>
                                <?php endforeach; ?>
                                <?php foreach ($package->php_tag_groups as $major => $tags) : ?>
                                    <span class="hidden sm:inline-flex badge badge-xs badge-secondary badge-soft font-mono">PHP <?= h($major) ?>.x</span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <span aria-hidden="true">↗</span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="border-t border-base-200 bg-base-200/50 px-4 py-2.5 flex items-center justify-between text-[11px] text-base-content/40">
            <span><kbd class="kbd kbd-xs">↑</kbd> <kbd class="kbd kbd-xs">↓</kbd> <?= __('navigate') ?> · <kbd class="kbd kbd-xs">esc</kbd> <?= __('close') ?></span>
            <span><?= __('Press Enter to search all packages') ?></span>
        </div>
    </div>
    <p class="bg-base-100 text-error rounded px-2 text-xs" x-show="$server.error" x-cloak role="alert" x-text="$server.error ? $server.error.message : ''"></p>
    <?= $this->Form->end() ?>
</div>
