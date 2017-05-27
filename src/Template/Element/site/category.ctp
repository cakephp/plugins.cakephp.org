<?php
if (empty($category->slug)) {
    return;
}
?>

<a href="/packages?category=<?php echo $category->slug; ?>" class="label category-label" style="background-color:<?php echo $category->getColor(); ?>">
    <?php echo $category->name; ?>
</a>&nbsp;
