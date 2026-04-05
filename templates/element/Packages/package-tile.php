<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Package $package
 * @var bool $isFeatured
 */
$isFeatured = $isFeatured ?? false;
$query = $this->getRequest()->getQueryParams();
$tagGroups = $package->cake_php_tag_groups;
krsort($tagGroups);
$existingSlugs = (array)($query['cakephp_slugs'] ?? []);
$packageId = preg_replace('/[^a-z0-9]/i', '-', strtolower($package->package));
$dialogId = 'compat-' . $packageId;
?>
<article class="card h-full rounded-3xl border border-base-300 bg-base-100 shadow-sm transition hover:-translate-y-1 hover:shadow-lg overflow-hidden">
    <a class="card-header block bg-base-200 px-5 py-4 transition hover:bg-base-100 border-b border-base-300"
       target="_blank" rel="noopener noreferrer" href="<?= h($package->repo_url) ?>">
        <div class="flex items-start justify-start gap-4">
            <div class="min-w-0">
                <?php if ($isFeatured) : ?>
                    <div class="mb-1">
                        <span class="badge badge-error badge-xs text-white uppercase">
                            <?= __('Featured') ?>
                        </span>
                    </div>
                <?php endif; ?>
                <h2 class="card-title truncate text-lg transition hover:text-cake-red">
                    <?= h($package->package) ?>
                </h2>
            </div>
        </div>
    </a>

    <div class="card-body flex flex-1 flex-col">
        <p class="min-h-18 flex-1 text-sm leading-6 line-clamp-4">
            <?= h($package->description ?: __('No description available.')) ?>
        </p>

        <?php if ($package->cake_php_tags): ?>
            <div class="mt-5 border-t border-dashed pt-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] opacity-60">
                    <?= __('CakePHP Compatibility') ?>
                </p>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($tagGroups as $majorVersion => $tags): ?>
                        <?php
                        $groupSlugs = array_keys(array_column($tags, null, 'slug'));
                        $isActive = (bool)array_intersect($existingSlugs, $groupSlugs);
                        ?>
                        <button type="button"
                                onclick="document.getElementById('<?= h($dialogId) ?>').showModal()"
                                class="btn btn-xs <?= $isActive ? 'btn-primary' : 'btn-soft btn-primary' ?>">
                            <?= h($majorVersion) ?>.x
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-actions w-full p-0">
        <div class="stats stats-vertical sm:stats-horizontal w-full rounded-none bg-base-200 border-t border-base-300">
            <div class="stat px-4 py-3">
                <div class="stat-title text-xs flex items-center gap-2 [&_svg]:size-4">
                    <?= file_get_contents(WWW_ROOT . 'img' . DS . 'download.svg') ?: '' ?>
                    <?= __('Downloads') ?>
                </div>
                <div class="stat-value text-base">
                    <?= $this->Number->format($package->downloads) ?>
                </div>
            </div>
            <div class="stat px-4 py-3">
                <div class="stat-title text-xs">
                    <?= __('Latest Version') ?>
                </div>
                <div class="stat-value text-base">
                    <?= h($package->latest_stable_version ?: __('Unknown')) ?>
                </div>
            </div>
            <div class="stat px-4 py-3">
                <div class="stat-title text-xs flex items-center gap-2 [&_svg]:size-4">
                    <?= file_get_contents(WWW_ROOT . 'img' . DS . 'star.svg') ?: '' ?>
                    <?= __('Stars') ?>
                </div>
                <div class="stat-value text-base">
                    <?= $this->Number->format($package->stars) ?>
                </div>
            </div>
        </div>
    </div>
</article>
<?php if ($package->cake_php_tags): ?>
    <dialog id="<?= h($dialogId) ?>" class="modal">
        <div class="modal-box max-w-md">
            <h3 class="mb-1 text-base font-semibold"><?= h($package->package) ?></h3>
            <p class="mb-4 text-xs opacity-50"><?= __('Filter by CakePHP version') ?></p>
            <div class="space-y-4">
                <?php foreach ($tagGroups as $majorVersion => $tags): ?>
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider opacity-50"><?= __('CakePHP {0}.x', h($majorVersion)) ?></p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($tags as $tag): ?>
                                <?php
                                $slug = $tag->slug;
                                $tagIsActive = in_array($slug, $existingSlugs, true);
                                $tagQuery = $query;
                                $tagQuery['cakephp_slugs'] = $tagIsActive
                                    ? array_values(array_diff($existingSlugs, [$slug]))
                                    : array_values(array_unique(array_merge($existingSlugs, [$slug])));
                                unset($tagQuery['page']);
                                ?>
                                <a href="<?= h($this->Url->build(['?' => $tagQuery])) ?>"
                                   class="btn btn-xs <?= $tagIsActive ? 'btn-error' : 'btn-soft btn-error' ?>">
                                    <?= h(str_replace('CakePHP: ', '', $tag->label)) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn btn-sm btn-ghost"><?= __('Close') ?></button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button><?= __('Close') ?></button></form>
    </dialog>
<?php endif; ?>
