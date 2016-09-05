<section class="hero">
    <div class="container-fluid text-center">
        <div class="col-sm-12">
            <div class="title-home">
                <h3><?= __('Find, install and publish CakePHP plugins with the CakePHP Package Index')?></h3>
            </div>
        </div>
    </div>
</section>

<section class="ptb-30 fundo-w">
    <div class="container">
        <div class="row">
            <?php echo $this->element('site/categories', [], ['cache' => true]) ?>
        </div>
    </div>
</section>

<section class="hero-2 fundo-w">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <div class="row">
                    <div class="col-md-6">
                        <?php echo $this->cell('PackageList::featured', [], ['cache' => true]); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo $this->cell('PackageList::popular', [], ['cache' => true]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="ptb-80 back-red footer-wrapper">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <?php echo $this->element('site/suggest') ?>
            </div>
        </div>
    </div>
</section>
