<?php
use App\Service\AwesomeListImporter;

/**
 * @var \App\View\AppView|\App\View\IslandTemplateView $this
 * @var \App\Model\Entity\Package $package
 */
[$vendor, $name] = array_pad(explode('/', $package->package, 2), 2, '');
$packagistUrl = 'https://packagist.org/packages/' . implode('/', array_map(rawurlencode(...), explode('/', $package->package)));
$groups = [
    ['label' => 'CakePHP', 'query' => 'cakephp_slugs', 'tags' => $package->cake_php_tag_groups],
    ['label' => 'PHP', 'query' => 'php_slugs', 'tags' => $package->php_tag_groups],
];
?>
<article class="plugin-details" id="plugin-details-<?= (int)$package->id ?>">
    <header class="plugin-details-heading">
        <p class="plugin-vendor"><?= h($vendor) ?> /</p>
        <h2><?= h($name ?: $vendor) ?></h2>
        <?= $this->element('Packages/awesome_badge', compact('package')) ?>
        <p class="plugin-details-description"><?= h($package->description ?: __('No description available.')) ?></p>
        <div class="plugin-resource-links">
            <a class="btn btn-sm btn-primary" href="<?= h($package->repo_url) ?>" target="_blank" rel="noopener noreferrer">
                <?= $this->element('github_icon') ?>
                <?= __('Repository') ?> <span aria-hidden="true">↗</span>
            </a>
            <a class="btn btn-sm btn-outline" href="<?= h($packagistUrl) ?>" target="_blank" rel="noopener noreferrer">
                <?= __('Packagist') ?> <span aria-hidden="true">↗</span>
            </a>
        </div>
    </header>
    <dl class="plugin-stats">
        <div>
            <dt><?= __('Latest stable') ?></dt>
            <dd><?= h($package->latest_stable_version ?: __('Not listed')) ?></dd>
        </div>
        <div>
            <dt><?= __('Released') ?></dt>
            <dd>
                <?php if ($package->latest_stable_release_date) : ?>
                    <time datetime="<?= h($package->latest_stable_release_date->format('Y-m-d')) ?>">
                        <?= h($package->latest_stable_release_date->i18nFormat('MMM d, yyyy')) ?>
                    </time>
                <?php else : ?>
                    <?= __('Not listed') ?>
                <?php endif; ?>
            </dd>
        </div>
        <div>
            <dt><?= __('Downloads') ?></dt>
            <dd><?= $this->Number->format($package->downloads) ?></dd>
        </div>
        <div>
            <dt><?= __('Stars') ?></dt>
            <dd><?= $this->Number->format($package->stars) ?></dd>
        </div>
    </dl>
    <?php if ($package->categories) : ?>
        <section class="plugin-details-section plugin-category-section" aria-label="<?= __('Categories') ?>">
            <h3><?= __('Categories') ?></h3>
            <div class="plugin-category-links">
                <?php foreach ($package->categories as $category) : ?>
                    <a class="category-label" href="<?= $this->Url->build(['plugin' => null, 'controller' => 'Packages', 'action' => 'index', '?' => ['category' => $category->slug]]) ?>"
                       data-filter-package-category data-category-slug="<?= h($category->slug) ?>"><?= h($category->name) ?></a>
                <?php endforeach; ?>
            </div>
            <p class="plugin-section-hint">
                <?= __('Community-curated in') ?>
                <a href="<?= AwesomeListImporter::SOURCE_URL ?>" target="_blank" rel="noopener noreferrer">Awesome CakePHP</a>.
            </p>
        </section>
    <?php endif; ?>
    <section class="plugin-details-section" aria-label="<?= __('Installation') ?>">
        <h3><?= __('Install with Composer') ?></h3>
        <div class="plugin-install" x-data="packageInstall" id="package-install-<?= (int)$package->id ?>">
            <div class="plugin-install-command">
                <code x-ref="command">composer require <?= h($package->package) ?></code>
                <button type="button" class="btn btn-sm btn-ghost" @click="copy" :aria-label="copied ? '<?= __('Command copied') ?>' : '<?= __('Copy install command') ?>'">
                    <span x-show="!copied"><?= __('Copy') ?></span>
                    <span x-show="copied" x-cloak><?= __('Copied') ?></span>
                </button>
            </div>
            <p :class="failed ? 'plugin-copy-feedback' : 'sr-only'" role="status" x-show="copied || failed" x-cloak
               x-text="failed ? '<?= __('Select the command above to copy it.') ?>' : '<?= __('Install command copied to clipboard.') ?>'"></p>
        </div>
    </section>
    <section class="plugin-details-section plugin-version-section" aria-label="<?= __('Compatibility') ?>">
        <h3><?= __('Compatibility') ?></h3>
        <?php if ($package->cake_php_tags || $package->php_tags) : ?>
            <p class="plugin-section-hint"><?= __('Choose a version to filter the directory.') ?></p>
        <?php endif; ?>
        <?php foreach ($groups as $group) : ?>
            <div class="plugin-version-group">
                <h4><?= h($group['label']) ?></h4>
                <div class="compatibility-versions" role="group" aria-label="<?= __('{0} compatibility', $group['label']) ?>">
                    <?php if ($group['tags'] === []) : ?>
                        <span class="plugin-section-hint"><?= __('Not listed') ?></span>
                    <?php endif; ?>
                    <?php foreach ($group['tags'] as $tags) : ?>
                        <?php foreach ($tags as $tag) : ?>
                            <?php $version = str_replace($group['label'] . ': ', '', $tag->label); ?>
                            <a class="version-pill"
                               aria-label="<?= h($group['label'] . ' ' . $version) ?>"
                               href="<?= $this->Url->build(['controller' => 'Packages', 'action' => 'index', 'plugin' => null, '?' => [$group['query'] => [$tag->slug]]]) ?>"
                               data-filter-package-version data-version-group="<?= h($group['query']) ?>" data-version-slug="<?= h($tag->slug) ?>">
                                <?= h($version) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</article>
