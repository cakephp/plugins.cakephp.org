<?php
/** @var \App\View\AppView $this */
$this->assign('title', __('Publishers'));
?>
<div class="site-width pt-6 md:pt-10">
    <header class="page-intro">
        <h1><?= __('Publishers') ?></h1>
        <p><?= __('Discover the people and teams behind CakePHP plugins, ranked by current usage and compatibility.') ?></p>
    </header>
</div>
<?= $this->Interact->render('Publishers', key: 'publishers-directory') ?>
<?= $this->element('Packages/plugin-modal', ['catalogueFilters' => false]) ?>
