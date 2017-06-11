<h2 class="package-results-header"><?php echo (empty($category)) ? __('Available CakePHP packages') : __('CakePHP {0} packages', $category->name); ?></h2>
<div class="package-total">
    <?php
        $count = $this->Paginator->counter(['format' => '{{count}}']);
        echo sprintf(__n('%s package found', '%s packages found', str_replace(',', '', $count)), $count);
    ?>
</div>

<?php echo $this->element('site/package-results', ['packages' => $packages]) ?>
