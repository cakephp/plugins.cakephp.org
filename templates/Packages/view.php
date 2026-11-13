<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Package $package
 */
$this->assign('title', $package->package);
[$namespace, $name] = explode('/', $package->package, 2);
$this->assign('canonicalUrl', $this->Url->build([
    '_name' => 'plugin:view',
    'namespace' => $namespace,
    'name' => $name,
], ['fullBase' => true]));
?>
<div class="plugin-page site-width">
    <h1 class="sr-only"><?= h($package->package) ?></h1>
    <a class="mb-5 inline-flex items-center gap-2 text-sm leading-[1.6]" href="<?= $this->Url->build(['_name' => 'plugin:index']) ?>" x-interact:navigate>
        <span aria-hidden="true">←</span> <?= __('Browse plugins') ?>
    </a>
    <div class="plugin-page-panel">
        <?= $this->element('Packages/plugin-details', compact('package')) ?>
    </div>
</div>
