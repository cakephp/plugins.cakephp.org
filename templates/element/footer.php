<?php
/**
 * @var \App\View\AppView $this
 * @var bool|null $minimal
 */
$minimal = $minimal ?? false;
?>
<footer class="site-footer">
    <div class="site-width">
        <?php if (!$minimal) : ?>
            <div class="footer-overview">
                <div>
                    <?= $this->element('brand') ?>
                    <p class="mt-3 max-w-sm text-sm leading-relaxed text-muted"><?= __('Discover plugins built by the CakePHP community.') ?></p>
                </div>
                <?= $this->Interact->render('Ecosystem', key: 'ecosystem-snapshot') ?>
            </div>
        <?php endif; ?>
        <div class="footer-bottom<?= !$minimal ? ' border-t border-base-300' : '' ?>">
            <p><?= __('CakePHP plugin directory') ?></p>
            <nav aria-label="<?= __('Resources') ?>">
                <?= $this->Html->link(__('Docs'), 'https://book.cakephp.org/', ['target' => '_blank', 'rel' => 'noopener']) ?>
                <?= $this->Html->link(__('API'), 'https://api.cakephp.org/', ['target' => '_blank', 'rel' => 'noopener']) ?>
            </nav>
        </div>
    </div>
</footer>
