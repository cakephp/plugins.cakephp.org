<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var \Cake\Datasource\Paging\PaginatedInterface<int, array<string, mixed>> $publishers
 * @var array<string, array{githubUrl: string|null, plugins: list<array{id: int, package: string, name: string}>}> $details
 * @var array<string, string> $sortOptions
 * @var array<string, string> $versionOptions
 * @var string|null $selectedMajor
 * @var string $search
 * @var string $sort
 * @var string $cakephp
 * @var bool $showRanks
 * @var int $rankStart
 */
$this->Form->setTemplates([
    'input' => '<input type="{{type}}" name="{{name}}"{{attrs}}>',
    'select' => '<select name="{{name}}"{{attrs}}>{{content}}</select>',
    'button' => '<button{{attrs}}>{{text}}</button>',
]);
$clearQuery = [];
if ($sort !== 'monthly_downloads') {
    $clearQuery['sort'] = $sort;
}
if ($cakephp !== 'current') {
    $clearQuery['cakephp'] = $cakephp;
}
$clearUrl = $this->Url->build([
    '_name' => 'publisher:index',
    '?' => $clearQuery,
], [
    'escape' => false,
]);
$statLabels = [
    'plugins' => __('Plugins'),
    ($sort === 'downloads' ? 'downloads' : 'monthly_downloads') => $sort === 'downloads' ? __('Lifetime downloads') : __('Monthly downloads'),
    'stars' => __('Stars'),
];
?>
<div class="publishers-directory pt-4 md:pt-5 site-width" x-data>
    <h1 class="sr-only"><?= __('Publishers') ?></h1>
    <?= $this->Form->create(null, [
        'type' => 'get',
        'url' => ['_name' => 'publisher:index'],
        'class' => 'publisher-controls',
        'id' => 'publisher-search-form',
        'role' => 'search',
        'aria-label' => __('Find publishers'),
        'x-interact:submit' => '$server.sync()',
    ]) ?>
        <div class="flex min-w-0 items-center gap-3 md:gap-4">
            <label class="search-control input min-w-0 flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m16 16 4.5 4.5" stroke-linecap="round"></path>
                </svg>
                <span class="sr-only"><?= __('Search publishers') ?></span>
                <?= $this->Form->search('search', [
                    'value' => $search,
                    'placeholder' => __('Search by namespace'),
                    'maxlength' => 100,
                    'autocomplete' => 'off',
                    'class' => 'w-full',
                    'aria-controls' => 'publisher-results',
                    'x-interact:model.live' => 'search',
                ]) ?>
                <span class="loading loading-spinner loading-sm shrink-0" role="status" aria-label="<?= __('Updating publishers') ?>"
                      x-show="!$server.error && ($server.dirty || $server.pending())" x-cloak></span>
            </label>
            <?php if (trim($search) !== '') : ?>
                <a class="reset-filters" href="<?= h($clearUrl) ?>" @click.prevent="$server.call('clearSearch')"
                   aria-label="<?= __('Clear search') ?>" title="<?= __('Clear search') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path d="M3 10a9 9 0 1 1 2.6 8.4M3 4v6h6" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span><?= __('Clear search') ?></span>
                </a>
            <?php endif; ?>
        </div>
        <label class="min-w-0">
            <span class="sr-only"><?= __('CakePHP version') ?></span>
            <?= $this->Form->select('cakephp', $versionOptions, [
                'value' => $cakephp,
                'class' => 'catalogue-filter-select select min-w-0',
                'data-filter-active' => 'true',
                'aria-label' => __('CakePHP version'),
                'x-interact:model.live' => 'cakephp',
            ]) ?>
        </label>
        <?= $this->Form->hidden('sort', ['value' => $sort]) ?>
        <noscript class="md:col-span-2"><?= $this->Form->button(__('Apply'), [
            'class' => 'btn btn-primary',
        ]) ?></noscript>
    <?= $this->Form->end() ?>

    <p class="mb-4 text-sm text-muted" data-publisher-scope>
        <?= $selectedMajor !== null
            ? __('Counts and rankings include plugins compatible with CakePHP {0}.', h($selectedMajor))
            : __('Counts and rankings include plugins for all CakePHP versions.') ?>
        <?= __('Monthly downloads cover all releases of those packages. Missing figures appear after the next package sync.') ?>
    </p>
    <section class="catalogue-panel" aria-label="<?= __('Publisher rankings') ?>">
        <header class="catalogue-toolbar">
            <div class="catalogue-summary">
                <h2><span class="result-count badge badge-soft badge-primary"><?= $this->Paginator->counter('{{count}}') ?></span> <?= __('publishers') ?></h2>
            </div>
            <div class="sort-control text-sm">
                <span class="text-muted"><?= __('Rank by') ?></span>
                <details class="sort-menu dropdown dropdown-end" x-data="publisherSort" @click="select($event)" @keydown.escape="close">
                    <summary aria-label="<?= __('Rank publishers by') ?>">
                        <?= h($sortOptions[$sort]) ?>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="m4 6 4 4 4-4" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </summary>
                    <div class="sort-options dropdown-content">
                        <?php foreach ($sortOptions as $value => $label) : ?>
                            <?php
                            $sortSelected = $value === $sort;
                            $sortQuery = $clearQuery;
                            $sortQuery['sort'] = $value;
                            if ($search !== '') {
                                $sortQuery['search'] = $search;
                            }
                            $sortUrl = $this->Url->build([
                                '_name' => 'publisher:index',
                                '?' => $sortQuery,
                            ]);
                            ?>
                            <a class="sort-option<?= $sortSelected ? ' sort-option-active' : '' ?>" data-publisher-sort="<?= h($value) ?>"
                               href="<?= $sortUrl ?>" <?= $sortSelected ? 'aria-current="true"' : '' ?>><?= h($label) ?></a>
                        <?php endforeach; ?>
                    </div>
                </details>
            </div>
        </header>
        <div class="catalogue-error" role="alert" x-show="$server.error" x-cloak>
            <p><?= __('We couldn’t update the publishers. Please try again.') ?></p>
            <?= $this->Form->button(__('Retry'), [
                'type' => 'button',
                'class' => 'action-link btn btn-outline',
                '@click' => '$server.sync()',
                ':disabled' => '$server.pending()',
            ]) ?>
        </div>
        <div id="publisher-results" :aria-busy="!$server.error && ($server.dirty || $server.pending()) ? 'true' : 'false'">
            <?php if (count($publishers) === 0) : ?>
                <div class="empty-results">
                    <h2><?= __('No publishers found') ?></h2>
                    <p><?= __('Try another namespace or clear your search.') ?></p>
                    <a class="action-link btn btn-outline" href="<?= h($clearUrl) ?>" @click.prevent="$server.call('clearSearch')"><?= __('Clear search') ?></a>
                </div>
            <?php else : ?>
                <ol aria-label="<?= __('Publishers') ?>">
                    <?php foreach ($publishers as $publisher) : ?>
                        <?php
                        $namespace = $publisher['namespace'];
                        $detail = $details[$namespace];
                        ?>
                        <li class="publisher-row" data-publisher-namespace="<?= h($namespace) ?>">
                            <div class="flex min-w-0 items-start gap-3 md:gap-4">
                                <div class="avatar avatar-placeholder relative shrink-0">
                                    <?= $this->Github->avatar($namespace, $detail['githubUrl'], [
                                        'class' => 'publisher-avatar',
                                        'size' => 64,
                                        'requestSize' => 96,
                                        'imageAttributes' => [
                                            'loading' => 'lazy',
                                            'data-publisher-avatar' => '',
                                        ],
                                    ]) ?>
                                    <?php if ($showRanks) : ?>
                                        <span class="publisher-rank badge badge-sm" data-publisher-rank aria-label="<?= __('Rank {0}', $rankStart) ?>">#<?= $rankStart++ ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="publisher-name">
                                        <?= $this->Html->link($namespace, [
                                            '_name' => 'plugin:namespace',
                                            'namespace' => $namespace,
                                        ], ['x-interact:navigate' => '']) ?>
                                    </h2>
                                    <p class="mt-2 text-xs text-muted"><?= __('Popular plugins') ?></p>
                                    <ul class="mt-1.5 flex flex-wrap gap-1.5" aria-label="<?= __('Popular plugins by {0}', $namespace) ?>">
                                        <?php foreach ($detail['plugins'] as $plugin) : ?>
                                            <li class="min-w-0">
                                                <?= $this->Html->link($plugin['name'], [
                                                    '_name' => 'plugin:view',
                                                    'namespace' => $namespace,
                                                    'name' => $plugin['name'],
                                                ], [
                                                    'class' => 'publisher-plugin badge badge-sm badge-soft',
                                                    'aria-label' => __('View {0} details', $plugin['package']),
                                                    'data-open-plugin' => true,
                                                    'data-plugin-id' => $plugin['id'],
                                                    'data-plugin-name' => $plugin['package'],
                                                ]) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php if ($publisher['latest_release'] !== null) : ?>
                                        <p class="mt-2 text-xs text-muted">
                                            <?= __('Latest release') ?>
                                            <time datetime="<?= h($publisher['latest_release']) ?>"><?= h($this->Time->format($publisher['latest_release'], 'MMM d, yyyy')) ?></time>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <dl class="publisher-statistics">
                                <?php foreach ($statLabels as $key => $label) : ?>
                                    <div class="min-w-0">
                                        <dt class="text-xs text-muted"><?= h($label) ?></dt>
                                        <dd class="mt-1 text-lg font-semibold tabular-nums" data-publisher-stat="<?= h($key) ?>"
                                            title="<?= $publisher[$key] !== null ? h($this->Number->format($publisher[$key])) : __('Awaiting package sync') ?>"
                                            aria-label="<?= $publisher[$key] !== null ? h($this->Number->format($publisher[$key])) : __('Awaiting package sync') ?>">
                                            <?= $publisher[$key] !== null ? h($this->Number->compact((int)$publisher[$key])) : '—' ?>
                                        </dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>
        <?php if (count($publishers) > 0) : ?>
            <div class="catalogue-pagination">
                <p><?= $this->Paginator->counter(__('Showing {{start}}–{{end}} of {{count}} publishers')) ?></p>
                <nav class="flex flex-wrap items-center gap-1.5" aria-label="<?= __('Publisher pages') ?>">
                    <?= $this->Paginator->prev(__('Previous')) ?>
                    <?= $this->Paginator->numbers(['modulus' => 2]) ?>
                    <?= $this->Paginator->next(__('Next')) ?>
                </nav>
            </div>
        <?php endif; ?>
    </section>
</div>
