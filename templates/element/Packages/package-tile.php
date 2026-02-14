<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Package $package
 */
?>
<div class="border-2 rounded-2xl border-black flex flex-col">
    <a class="block text-center text-xl p-2 bg-gray-300 rounded-t-xl hover:underline"
       target="_blank" href="<?= $package->repo_url ?>">
        <?= h($package->package) ?>
    </a>
    <div class="flex flex-col flex-auto p-4 space-y-4">
        <div>
            <?= h($package->description) ?><br/>
        </div>

        <div class="flex-auto">
            <div class="grid grid-cols-[80px_auto] gap-2">
                <?php if ($package->cake_php_tags): ?>
                    <div><?= __('CakePHP:') ?></div>
                    <div class="flex flex-wrap gap-1">
                        <?php foreach($package->cake_php_tags as $tag): ?>
                            <a href="?slug=<?= $tag->slug ?>" class="text-xs rounded-3xl px-2 py-1 text-white bg-cake-red">
                                <?= str_replace('CakePHP: ', '', $tag->label) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
<!--                <div>--><?php //= __('PHP:') ?><!--</div>-->
<!--                <div class="flex flex-wrap gap-1">-->
<!--                    --><?php //foreach($package->php_tags as $tag): ?>
<!--                        <a href="?slug=--><?php //= $tag->slug ?><!--" class="text-xs rounded-3xl px-2 py-1 text-white bg-blue-500">-->
<!--                            --><?php //= str_replace('PHP: ', '', $tag->label) ?>
<!--                        </a>-->
<!--                    --><?php //endforeach; ?>
<!--                </div>-->
            </div>
        </div>

        <div class="flex justify-between [&_svg]:w-6">
            <div class="flex gap-2">
                <?php include WWW_ROOT . 'img' . DS . 'download.svg'; ?>
                <?= $this->Number->format($package->downloads) ?>
            </div>
            <div class="flex gap-2">
                <?= __('Latest Version:') ?>
                <?= $package->latest_stable_version ?>
            </div>
            <div class="flex gap-2">
                <?php include WWW_ROOT . 'img' . DS . 'star.svg'; ?>
                <?= $this->Number->format($package->stars) ?>
            </div>
        </div>
    </div>
</div>
