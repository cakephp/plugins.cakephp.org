<?php
/**
 * @var \App\View\IslandTemplateView $this
 * @var string $packageName
 * @var string|null $logo
 */
$shortName = explode('/', $packageName, 2)[1] ?? $packageName;
$words = preg_split('/[-_]+/', preg_replace('/^cakephp[-_]/i', '', $shortName) ?: $shortName) ?: [$shortName];
$initials = mb_substr($words[0], 0, 1);
$initials .= count($words) > 1 ? mb_substr($words[1], 0, 1) : mb_substr($words[0], 1, 1);
?>
<div class="avatar avatar-placeholder shrink-0 self-start" aria-hidden="true">
    <div class="plugin-avatar-tile">
        <span><?= h(mb_strtoupper($initials)) ?></span>
        <?php if ($logo !== null) : ?>
            <?= $this->Html->image($logo, [
                'alt' => '', 'width' => 48, 'height' => 48, 'loading' => 'lazy', 'decoding' => 'async',
                '@error' => '$el.remove()',
            ]) ?>
        <?php endif; ?>
    </div>
</div>
