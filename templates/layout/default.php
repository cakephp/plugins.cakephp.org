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
<html class="bg-base-100">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>
    <link rel="canonical" href="<?= h($canonicalUrl) ?>">

    <?= $this->Html->css(['cake']) ?>

    <?= $this->Html->script('app.js', ['type' => 'module']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body class="overflow-x-hidden bg-base-100" hx-indicator="#main-loading-overlay">
    <?= $this->element('navbar') ?>
    <div id="above-content-slot">
        <?php if ($this->fetch('above_content')) : ?>
            <div class="w-full border-b overflow-hidden border-cake-red/20 bg-cake-red/5">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <?= $this->fetch('above_content') ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div id="main-loading-overlay" class="htmx-indicator main-loading-overlay" aria-hidden="true">
        <div class="main-loading-indicator-card flex items-center gap-3 rounded-2xl border border-base-300 bg-base-100/95 px-4 py-3 shadow-2xl">
            <span class="loading loading-ring loading-xl text-cake-red" aria-hidden="true"></span>
            <span class="text-sm font-semibold text-base-content"><?= __('Loading content...') ?></span>
        </div>
    </div>
    <main class="main container mx-auto">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </main>
</body>
</html>
