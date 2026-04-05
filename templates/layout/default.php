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
$isPackagesIndex = $request->getParam('controller') === 'Packages' && $request->getParam('action') === 'index';
$searchValue = (string)$request->getQuery('search', '');
$cakephpSlugs = (array)$request->getQuery('cakephp_slugs', []);
$phpSlugs = (array)$request->getQuery('php_slugs', []);
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

    <?= $this->Html->css(['cake']) ?>

    <?= $this->Html->script('app.js', ['type' => 'module']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body class="overflow-x-hidden bg-base-100" hx-indicator="#main-loading-overlay">
    <div class="sticky top-0 z-40 border-b border-cake-red/70 bg-cake-red/90 backdrop-blur-md">
        <div class="navbar container mx-auto px-4 sm:px-6 lg:px-8 gap-5">
            <div class="navbar-start">
                <a class="text-xl" href="<?= $this->Url->build('/') ?>">
                    <img src="/img/cake-logo.png" class="w-28" alt="CakePHP Logo"/>
                </a>
            </div>
            <?php
            $searchFormOptions = [
                'type' => 'get',
                'url' => ['controller' => 'Packages', 'action' => 'index'],
                'class' => 'w-full',
                'valueSources' => 'query', // Read existing values from query string
            ];
            if ($isPackagesIndex) {
                $searchFormOptions += [
                    'hx-get' => $this->Url->build(['controller' => 'Packages', 'action' => 'index']),
                    'hx-target' => '#packages-index-content',
                    'hx-select' => '#packages-index-content',
                    'hx-swap' => 'outerHTML',
                    'hx-push-url' => 'true',
                ];
            }
            echo $this->Form->create(null, $searchFormOptions);
            // Preserve filter parameters when searching
            foreach ($cakephpSlugs as $slug) {
                echo $this->Form->hidden('cakephp_slugs[]', ['value' => $slug]);
            }
            foreach ($phpSlugs as $slug) {
                echo $this->Form->hidden('php_slugs[]', ['value' => $slug]);
            }
            ?>
            <div class="join w-full">
                <label class="input join-item w-full bg-white text-base-content">
                    <svg class="h-4 w-4 shrink-0 opacity-60" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                        <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </g>
                    </svg>
                    <input type="search" name="search" value="<?= h($searchValue) ?>" placeholder="<?= __('Search packages...') ?>" class="grow min-w-0" />
                </label>
            </div>
            <?= $this->Form->end() ?>
            <div class="navbar-end">
                <ul class="menu menu-horizontal hidden px-1 lg:flex">
                    <li>
                        <?= $this->Html->link('Docs', 'https://book.cakephp.org/', [
                            'target' => '_blank',
                            'rel' => 'noopener',
                            'class' => 'text-white'
                        ]) ?>
                    </li>
                    <li>
                        <?= $this->Html->link('Api', 'https://api.cakephp.org/', [
                            'target' => '_blank',
                            'rel' => 'noopener',
                            'class' => 'text-white'
                        ]) ?>
                    </li>
                </ul>
                <div class="dropdown dropdown-end lg:hidden">
                    <label tabindex="0" class="btn btn-ghost btn-circle hover:bg-cake-red/80 group:bg-cake-red:90 text-white" aria-label="<?= __('Open menu') ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </label>
                    <ul tabindex="0" class="menu menu-sm dropdown-content z-50 mt-3 w-40 rounded-box border border-base-200 bg-base-100 p-2 shadow-lg text-base-content">
                        <li><?= $this->Html->link('Docs', 'https://book.cakephp.org/', ['target' => '_blank', 'rel' => 'noopener']) ?></li>
                        <li><?= $this->Html->link('Api', 'https://api.cakephp.org/', ['target' => '_blank', 'rel' => 'noopener']) ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div id="above-content-slot">
        <?php if ($this->fetch('above_content')) : ?>
            <div class="w-full border-b border-cake-red/20 bg-cake-red/5">
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
