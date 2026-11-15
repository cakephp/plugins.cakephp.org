<?php
/** @var \App\View\AppView $this */
$this->assign('title', __('Publishers'));
echo $this->Interact->render('Publishers', key: 'publishers-directory');
echo $this->element('Packages/plugin-modal', ['catalogueFilters' => false]);
