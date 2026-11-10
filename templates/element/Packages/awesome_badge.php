<?php
/**
 * @var \App\View\AppView|\App\View\IslandTemplateView $this
 * @var \App\Model\Entity\Package $package
 */
?>
<?php if ($package->categories) : ?>
    <span class="awesome-badge badge badge-soft badge-primary" title="<?= __('Listed in the community-curated Awesome CakePHP list') ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path d="m12 3 2.8 5.7 6.3.9-4.6 4.5 1.1 6.3-5.6-3-5.6 3 1.1-6.3-4.6-4.5 6.3-.9L12 3Z" stroke-linejoin="round"></path>
        </svg>
        <?= __('Awesome CakePHP') ?>
    </span>
<?php endif; ?>
