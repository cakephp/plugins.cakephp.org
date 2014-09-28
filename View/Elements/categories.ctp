<?php
$categories = ClassRegistry::init('Category')->find('list', array(
    'fields' => array('slug', 'name'),
    'order' => array('slug'),
));
?>
<?php $categoryChunks = array_chunk($categories, ceil(count($categories) / 5), true); ?>
<div class="category-container">
    <?php foreach ($categoryChunks as $chunk => $categories) : ?>
        <ul>
            <?php foreach ($categories as $slug => $name) : ?>
            <li>
                <?php echo $this->Html->link($name, array(
                	'admin' => false,
                    'controller' => 'packages',
                    'action' => 'index',
                    '?' => array('category' => $slug)
                )); ?>
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
