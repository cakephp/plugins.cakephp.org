<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Package> $packages
 * @var iterable<\Tags\Model\Entity\Tag> $cakephpTags
 * @var iterable<\Tags\Model\Entity\Tag> $phpTags
 */
?>
<div class="packages index content">
    <div>
        <div class="flex flex-wrap lg:justify-between justify-center items-center my-8">
            <h3 class="text-xl"><?= __('Packages') ?></h3>
            <?php
            echo $this->Form->create(null, ['valueSources' => 'query', 'class' => 'flex flex-wrap justify-center gap-4']);
            echo $this->Form->control('search', ['label' => false, 'placeholder' => __('Search...')]);
            echo $this->Form->control('cakephp_slugs', [
                'label' => false,
                'options' => $cakephpTags,
                'empty' => __('No Filter'),
                'multiple' => true,
                'data-placeholder' => __('CakePHP Version'),
            ]);
//            echo $this->Form->control('php_slugs', [
//                'label' => false,
//                'options' => $phpTags,
//                'empty' => __('No Filter'),
//                'multiple' => true,
//                'data-placeholder' => __('PHP Version'),
//                'data-is-php-filter' => true,
//            ]);
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

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($packages as $package) : ?>
                <?= $this->element('Packages/package-tile', ['package' => $package]) ?>
            <?php endforeach; ?>
        </div>

    </div>
    <div class="py-7 flex flex-wrap justify-between items-center gap-3">
        <p class="text-sm text-slate-500"><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>

        <ul class="flex flex-wrap items-center gap-3">
            <?= $this->Paginator->first('« ' . __('first')) ?>
            <?= $this->Paginator->prev('‹ ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' ›') ?>
            <?= $this->Paginator->last(__('last') . ' »') ?>
        </ul>
    </div>
</div>
