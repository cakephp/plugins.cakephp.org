<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 */

$cakeDescription = 'CakePHP Plugins';
$request = $this->getRequest();
$theme = $request->getCookie('theme') === 'cakephp-dark' ? 'cakephp-dark' : 'cakephp';
$canonicalUrl = $this->Url->build($request->getPath() ?: '/', ['fullBase' => true]);
?>
<!DOCTYPE html>
<html data-theme="<?= h($theme) ?>">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>
    <link rel="canonical" href="<?= h($canonicalUrl) ?>">
    <?php if ($request->getQueryParams() !== []) : ?>
        <meta name="robots" content="noindex,nofollow">
    <?php endif; ?>

    <?= $this->Vite->css([
        'files' => ['resources/css/style.css'],
        'block' => false,
        'attributes' => ['x-interact:asset' => 'reload', 'timestamp' => false],
    ]) ?>
    <?= $this->Vite->script([
        'files' => ['resources/js/app.js'],
        'block' => false,
        'attributes' => ['x-interact:asset' => 'reload', 'timestamp' => false],
    ]) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <a class="skip-link" href="#main-content"><?= __('Skip to content') ?></a>
    <?= $this->element('navbar') ?>
    <div class="pointer-events-none fixed inset-x-0 top-0 z-[100] h-1 overflow-hidden"
         x-data x-show="$store.interactNavigation.loading()" x-cloak
         role="progressbar" aria-label="<?= __('Loading page') ?>">
        <div class="page-loading-bar"></div>
    </div>
    <main id="main-content" tabindex="-1">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </main>
    <?= $this->element('footer') ?>
</body>
</html>
