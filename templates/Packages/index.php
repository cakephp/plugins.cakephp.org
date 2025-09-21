<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Package> $packages
 * @var iterable<\Tags\Model\Entity\Tag> $tags
 */
?>
<div class="packages index content">
    <div>
        <div class="flex justify-between items-center my-8">
            <h3 class="text-xl"><?= __('Packages') ?></h3>
            <?php
            echo $this->Form->create(null, ['valueSources' => 'query', 'class' => 'flex gap-4']);
            echo $this->Form->control('search', ['label' => false,]);
            echo $this->Form->control('slug', [
                'label' => false,
                'options' => $tags,
                'empty' => __('No Filter'),
                'multiple' => true,
            ]);
            echo $this->Form->button('Search', ['type' => 'submit']);
            echo $this->Form->end();
            ?>
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
                <div class="border-2 rounded-2xl border-black p-4 flex flex-col justify-between">
                    <div>
                        <div class="text-center text-xl mb-2"><?= h($package->package) ?></div>
                        <div class="flex justify-center gap-4 mb-4">
                            <a class="underline" target="_blank" href="<?= $package->repo_url ?>"><?= __('Repository') ?></a>
                            <a class="underline" target="_blank" href="<?= $package->packagist_url ?>"><?= __('Packagist') ?></a>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-4">
                        <?php foreach($package->tags as $tag): ?>
                            <a href="?slug=<?= $tag->slug ?>" class="text-xs rounded-3xl px-2 py-1 text-white
                            <?= str_starts_with($tag->label, 'PHP') ? 'bg-blue-400' : 'bg-red-500' ?>">
                                <?= $tag->label ?>
                            </a>
                        <?php endforeach; ?>
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
