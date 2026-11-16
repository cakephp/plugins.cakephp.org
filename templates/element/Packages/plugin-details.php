<?php
use App\Service\AwesomeListImporter;
use App\Utility\CakephpVersion;
use Tags\Model\Entity\Tag;

/**
 * @var \App\View\AppView|\App\View\IslandTemplateView $this
 * @var \App\Model\Entity\Package $package
 */
[$vendor, $name] = array_pad(explode('/', $package->package, 2), 2, '');
$packagistUrl = 'https://packagist.org/packages/' . implode('/', array_map(rawurlencode(...), explode('/', $package->package)));
$developmentTags = array_filter($package->cake_php_tags, static fn(Tag $tag): bool => CakephpVersion::isDevelopmentLabel(str_replace('CakePHP: ', '', $tag->label)));
$development = $package->development_compatibility ?? [];
$developmentMajor = array_key_first($development);
$installCommand = 'composer require ' . $package->package;
if ($package->latest_stable_version === null && $developmentMajor !== null) {
    $installCommand = 'composer require ' . escapeshellarg($package->package . ':' . $development[$developmentMajor]['version'])
        . ' ' . escapeshellarg('cakephp/cakephp:' . $developmentMajor . '.x-dev');
}
$groups = [
    ['label' => 'CakePHP', 'query' => 'cakephp_slugs', 'tags' => [...$package->cake_php_tag_groups, ...($developmentTags !== [] ? [$developmentTags] : [])]],
    ['label' => 'PHP', 'query' => 'php_slugs', 'tags' => $package->php_tag_groups],
];
?>
<article class="plugin-details" id="plugin-details-<?= (int)$package->id ?>">
    <header class="plugin-details-heading">
        <p class="mb-1 text-[0.9375rem] font-medium text-muted wrap-anywhere">
            <?= $this->Html->link($vendor, [
                '_name' => 'plugin:namespace',
                'namespace' => $vendor,
            ], [
                'aria-label' => __('View all plugins by {0}', $vendor),
                'x-interact:navigate' => '',
            ]) ?> /
        </p>
        <h2><?= h($name ?: $vendor) ?></h2>
        <?= $this->element('Packages/awesome_badge', compact('package')) ?>
        <p class="mt-4 text-base leading-[1.7] text-base-content wrap-anywhere"><?= h($package->description ?: __('No description available.')) ?></p>
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
                    <a class="category-label badge badge-outline"
                       href="<?= $this->Url->build([
                           '_name' => 'plugin:index',
                           '?' => [
                               'category' => $category->slug,
                           ],
                       ]) ?>"
                       data-filter-package-category data-category-slug="<?= h($category->slug) ?>"><?= h($category->name) ?></a>
                <?php endforeach; ?>
            </div>
            <p class="plugin-section-hint">
                <?= __('Community-curated in') ?>
                <a href="<?= AwesomeListImporter::SOURCE_URL ?>" target="_blank" rel="noopener noreferrer">Awesome CakePHP</a>.
            </p>
        </section>
    <?php endif; ?>
    <?php if ($package->visible_keywords !== []) : ?>
        <section class="plugin-details-section" aria-label="<?= __('Keywords') ?>">
            <h3><?= __('Keywords') ?></h3>
            <div class="plugin-keyword-links">
                <?php foreach ($package->visible_keywords as $keyword) : ?>
                    <a class="keyword-link badge badge-outline"
                       href="<?= $this->Url->build(['_name' => 'plugin:index', '?' => ['keyword' => $keyword->name]]) ?>"
                       data-filter-package-keyword data-keyword-name="<?= h($keyword->name) ?>"><?= h($keyword->name) ?></a>
                <?php endforeach; ?>
            </div>
            <p class="plugin-section-hint"><?= __('Provided by the plugin author.') ?></p>
        </section>
    <?php endif; ?>
    <?php if ($package->latest_stable_version !== null || $developmentMajor !== null) : ?>
        <section class="plugin-details-section" aria-label="<?= __('Installation') ?>">
            <h3><?= __('Install with Composer') ?></h3>
            <?= $this->element('Packages/install_command', [
                'command' => $installCommand,
                'id' => 'package-install-' . $package->id,
            ]) ?>
        </section>
    <?php endif; ?>
    <section class="plugin-details-section plugin-version-section" aria-label="<?= __('Compatibility') ?>">
        <h3><?= __('Compatibility') ?></h3>
        <?php if ($package->cake_php_tags || $package->php_tags) : ?>
            <p class="plugin-section-hint"><?= __('Choose a version to filter the directory.') ?></p>
        <?php endif; ?>
        <?php foreach ($groups as $group) : ?>
            <div class="plugin-version-group">
                <h4><?= h($group['label']) ?></h4>
                <div class="flex flex-wrap gap-1.5 p-0.5" role="group" aria-label="<?= __('{0} compatibility', $group['label']) ?>">
                    <?php if ($group['tags'] === []) : ?>
                        <span class="plugin-section-hint"><?= __('Not listed') ?></span>
                    <?php endif; ?>
                    <?php foreach ($group['tags'] as $tags) : ?>
                        <?php foreach ($tags as $tag) : ?>
                            <?php $version = str_replace($group['label'] . ': ', '', $tag->label); ?>
                            <a class="version-pill badge badge-outline<?= CakephpVersion::isDevelopmentLabel($version) ? ' version-development' : '' ?>"
                               title="<?= h(CakephpVersion::isDevelopmentLabel($version) ? __('CakePHP {0} development builds', (int)$version) : $version) ?>"
                               aria-label="<?= h($group['label'] . ' ' . $version) ?>"
                               href="<?= $this->Url->build([
                                   '_name' => 'plugin:index',
                                   '?' => [
                                       $group['query'] => [$tag->slug],
                                   ],
                               ]) ?>"
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
