<?php
/** @var \App\View\IslandTemplateView $this */
?>
<div class="mt-5 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm opacity-70"><?= $this->Paginator->counter(__('Page {{page}} of {{pages}} · {{count}} records')) ?></p>
    <nav aria-label="<?= __('Pagination') ?>" class="flex gap-3">
        <?= $this->Paginator->prev(__('Previous')) ?>
        <?= $this->Paginator->next(__('Next')) ?>
    </nav>
</div>
