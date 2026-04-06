<?php
/**
 * @var \App\View\AppView $this
 * @var string $text
 * @var string|null $variant
 */
$variant = $variant ?? 'default';
$classes = 'inline-flex items-center rounded-full border px-2 py-0.5 text-[0.8rem] font-semibold';

if ($variant === 'accent') {
    $classes .= ' border-cake-red/25 bg-cake-red/10 text-cake-red';
} else {
    $classes .= ' border-base-300 bg-base-200 text-base-content';
}
?>
<code class="<?= h($classes) ?>"><?= h($text) ?></code>
