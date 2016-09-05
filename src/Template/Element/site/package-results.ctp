<?php
if (empty($packages)) {
    return;
}
$packageCount = count($packages);
$packages = new \Cake\Collection\Collection($packages);
$chunkSize = ceil($packageCount / 2);
?>
<section class="hero-2 fundo-w">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <div class="row">
                    <?php foreach ($packages->chunk($chunkSize) as $chunk => $packages) : ?>
                    <div class="col-md-6">
                        <?php echo $this->element('site/package-list', ['packages' => $packages]); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
