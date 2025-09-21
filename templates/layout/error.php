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
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <title>
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['cake']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <nav class="bg-amber-50 border-b border-red-600">
        <div class="flex justify-between max-w-4xl mx-auto py-4">
            <div class="top-nav-title">
                    <a class="text-xl" href="<?= $this->Url->build('/') ?>">
                        <span class="text-gray-700 font-bold">Cake</span><span class="text-red-800 font-bold">PHP</span>
                    </a>
            </div>
            <div class="flex gap-2">
                <?= $this->Html->link('Docs', 'https://book.cakephp.org/5/', ['target' => '_blank', 'rel' => 'noopener']) ?>
                <?= $this->Html->link('Api', 'https://api.cakephp.org/', ['target' => '_blank', 'rel' => 'noopener']) ?>
            </div>
        </div>
    </nav>
    <main class="main max-w-4xl mx-auto">
        <div class="container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
</body>
</html>
