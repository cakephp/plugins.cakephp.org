<?php
/** @var \App\View\AppView $this */
?>
<div x-data="pluginModal" @plugin-open.window="open($event)">
    <dialog class="modal" id="plugin-modal" x-ref="dialog" aria-labelledby="plugin-modal-label"
            @close="closed" @click="filter($event)">
        <div class="modal-box plugin-modal-box" x-ref="box">
            <div class="plugin-modal-bar">
                <span><?= __('Plugin details') ?></span>
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost" aria-label="<?= __('Close plugin details') ?>" autofocus>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path d="m6 6 12 12M6 18 18 6" stroke-linecap="round"></path>
                        </svg>
                    </button>
                </form>
            </div>
            <h2 id="plugin-modal-label" class="sr-only" x-text="title"></h2>
            <?= $this->Interact->render('PluginDetails') ?>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button aria-label="<?= __('Close plugin details backdrop') ?>"><?= __('Close') ?></button>
        </form>
    </dialog>
</div>
