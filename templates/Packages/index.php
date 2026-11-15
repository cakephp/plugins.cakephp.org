<?php
/** @var \App\View\AppView $this */
$this->assign('title', __('Discover CakePHP plugins'));
$this->assign('description', __('Find CakePHP plugins for your projects. Search by name, filter by CakePHP and PHP compatibility, and explore popular community packages.'));
$this->assign('canonicalUrl', $this->Url->build(['_name' => 'plugin:index'], ['fullBase' => true]));
?>
<h1 class="sr-only"><?= __('CakePHP plugins') ?></h1>
<?php
echo $this->Interact->render(
    'Catalogue',
    loading: 'lazy',
    placeholder: 'Loading/catalogue',
);
echo $this->element('Packages/plugin-modal');
