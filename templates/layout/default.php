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
$canonicalUrl = $this->Url->build($request->getPath() ?: '/', ['fullBase' => true]);
?>
<!DOCTYPE html>
<html class="bg-base-100" data-theme="cakephp">
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
    <?= $this->Html->script('theme', ['x-interact:asset' => 'reload']) ?>

    <?= $this->Html->css('cake.css?v=' . (is_file(WWW_ROOT . 'css/cake.css') ? filemtime(WWW_ROOT . 'css/cake.css') : 'dev'), ['x-interact:asset' => 'reload']) ?>

    <?= $this->Html->script('app.js?v=' . (is_file(WWW_ROOT . 'js/app.js') ? filemtime(WWW_ROOT . 'js/app.js') : 'dev'), ['defer' => true, 'x-interact:asset' => 'reload']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body class="overflow-x-hidden bg-base-100">
    <?= $this->element('navbar') ?>
    <div class="pointer-events-none fixed inset-x-0 top-0 z-100 h-1 overflow-hidden bg-base-100/30"
         x-data x-show="$store.interactNavigation.loading()" x-cloak
         role="progressbar" aria-label="<?= __('Loading page') ?>">
        <div class="page-loading-bar h-full w-1/3 bg-cake-blue"></div>
    </div>
    <main class="main">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </main>
</body>
</html>
