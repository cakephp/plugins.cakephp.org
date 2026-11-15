<?php
/** @var \App\View\AppView $this */
$this->assign('title', __('Publishers'));
$this->assign('description', __('Discover CakePHP plugin publishers. Explore popular packages, compare monthly downloads and stars, and find publishers supporting current CakePHP versions.'));
$this->assign('canonicalUrl', $this->Url->build(['_name' => 'publisher:index'], ['fullBase' => true]));
echo $this->Interact->render('Publishers', key: 'publishers-directory');
echo $this->element('Packages/plugin-modal', ['catalogueFilters' => false]);
