<h4 class="site-description">Browse By Category</h4>
<?php
$categories = \Cake\ORM\TableRegistry::get('Categories')->find()->order(['slug' => 'asc']);
?>
<div class="category-container">
    <?php foreach ($categories->chunk(9) as $chunk => $categories) : ?>
        <ul>
            <?php foreach ($categories as $category) : ?>
            <li>
                <?php echo $this->Html->link($category->name, [
                    'admin' => false,
                    'controller' => 'packages',
                    'action' => 'index',
                    '?' => ['category' => $category->slug]
                ]); ?>
            </li>
            <?php endforeach; ?>
            <?php
            $categoriesCount = count($categories);
            if ($categoriesCount != 9) {
                $categoriesCountLeft = 9 - $categoriesCount;
                while ($categoriesCountLeft > 0) {
                    $categoriesCountLeft--;
                    ?><li>&nbsp;</li><?php
                }
            }
            ?>
        </ul>
    <?php endforeach; ?>
</div>
