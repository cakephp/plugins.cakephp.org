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
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['cake']) ?>

    <script src="https://unpkg.com/slim-select@latest/dist/slimselect.js"></script>
    <link href="https://unpkg.com/slim-select@latest/dist/slimselect.css" rel="stylesheet">

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <nav class="bg-amber-50 border-b border-cake-red">
        <div class="flex justify-between container mx-auto p-4">
            <div class="top-nav-title">
                    <a class="text-xl" href="<?= $this->Url->build('/') ?>">
                        <span class="text-gray-700 font-bold">Cake</span><span class="text-cake-red font-bold">PHP</span>
                    </a>
            </div>
            <div class="flex gap-2">
                <?= $this->Html->link('Docs', 'https://book.cakephp.org/5/', ['target' => '_blank', 'rel' => 'noopener']) ?>
                <?= $this->Html->link('Api', 'https://api.cakephp.org/', ['target' => '_blank', 'rel' => 'noopener']) ?>
            </div>
        </div>
    </nav>
    <main class="main container mx-auto px-4">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </main>
    <script>
        const selects = document.querySelectorAll('select');
        selects.forEach((elem) => {
            let placeholder = elem.getAttribute('data-placeholder');
            new SlimSelect({
                select: elem,
                settings: {
                    placeholderText: placeholder,
                }
            })
        });
    </script>
</body>
</html>
