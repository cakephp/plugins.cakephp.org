<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Package $package
 */
$this->assign('title', $package->package);
?>
<div class="plugin-page site-width">
    <h1 class="sr-only"><?= h($package->package) ?></h1>
    <a class="plugin-back-link" href="<?= $this->Url->build(['controller' => 'Packages', 'action' => 'index']) ?>" x-interact:navigate>
        <span aria-hidden="true">←</span> <?= __('Browse plugins') ?>
    </a>
    <div class="plugin-page-panel">
        <?= $this->element('Packages/plugin-details', compact('package')) ?>
    </div>
</div>
