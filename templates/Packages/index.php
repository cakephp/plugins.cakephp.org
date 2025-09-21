<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Package> $packages
 */
?>
<div class="packages index content">
    <div>
        <div class="flex justify-between items-center my-8">
            <h3 class="text-xl"><?= __('Packages') ?></h3>
            <div class="flex gap-4">
                <p><?= __('Sort by:') ?></p>
                <div class="flex gap-4">
                    <?= $this->Paginator->sort('downloads') ?>
                    <?= $this->Paginator->sort('stars') ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-4">
            <?php foreach ($packages as $package): ?>
                <div class="border-2 rounded-2xl border-black p-4">
                    <div class="text-center text-xl mb-2"><?= h($package->package) ?></div>
                    <div class="flex justify-center gap-4 mb-4">
                        <a class="underline" target="_blank" href="<?= $package->repo_url ?>"><?= __('Repository') ?></a>
                        <a class="underline" target="_blank" href="<?= $package->packagist_url ?>"><?= __('Packagist') ?></a>
                    </div>
                    <div class="flex justify-between [&_svg]:w-6">
                        <div class="flex gap-2">
                            <?php include WWW_ROOT . 'img' . DS . 'download.svg'; ?>
                            <?= $this->Number->format($package->downloads) ?>
                        </div>
                        <div class="flex gap-2">
                            <?php include WWW_ROOT . 'img' . DS . 'star.svg'; ?>
                            <?= $this->Number->format($package->stars) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
    <div class="py-7 flex justify-between items-center gap-3">
        <p class="text-sm text-slate-500"><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>

        <ul class="flex items-center gap-3">
            <?= $this->Paginator->first('« ' . __('first')) ?>
            <?= $this->Paginator->prev('‹ ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' ›') ?>
            <?= $this->Paginator->last(__('last') . ' »') ?>
        </ul>
    </div>
</div>
