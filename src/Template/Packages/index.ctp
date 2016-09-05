<h2 class="package-results-header"><?php echo (empty($category)) ? __('Available CakePHP packages') : __('CakePHP {0} packages', $category->name); ?></h2>
<div class="package-total">
    <?php
        $count = count($packages);
        echo sprintf(__n('%d package found', '%d packages found', $count), $count);
    ?>
</div>

<?php echo $this->element('site/package-results', ['packages' => $packages]) ?>
