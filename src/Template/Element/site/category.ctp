<?php
if (empty($category->slug)) {
    return;
}
?>

<a href="/packages?category=<?php echo $category->slug; ?>" class="label category-label" style="<?php echo $this->Resource->styleTag($category->getColor()); ?>">
    <?php echo $category->name; ?>
</a>
