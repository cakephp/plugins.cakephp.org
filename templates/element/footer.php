<?php
/** @var \App\View\AppView $this */
?>
<footer class="site-footer site-width">
    <p><?= __('CakePHP plugin directory') ?></p>
    <nav aria-label="<?= __('Resources') ?>">
        <?= $this->Html->link(__('Docs'), 'https://book.cakephp.org/', ['target' => '_blank', 'rel' => 'noopener']) ?>
        <?= $this->Html->link(__('API'), 'https://api.cakephp.org/', ['target' => '_blank', 'rel' => 'noopener']) ?>
    </nav>
</footer>
