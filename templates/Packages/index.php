<?php
/** @var \App\View\AppView $this */
$this->assign('title', __('Packages'));
echo $this->Interact->render('Catalogue');
echo $this->element('Packages/plugin-modal');
