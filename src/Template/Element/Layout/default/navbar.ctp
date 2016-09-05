<header class="nav-down">
    <div class="container-fluid hidden-xs hidden-sm">
        <div class="row">
            <div class="col-sm-3 col-md-3">
                <?=
                $this->Html->link(
                    $this->Html->image('logo-cake.png', ['alt' => __('Home'), 'fullBase' => true]),
                    '/',
                    ['escape' => false, 'class' => 'logo-cake']
                ); ?>
            </div>
            <div class="col-sm-9 col-md-9">
                <nav class="navbar-right">
                    <?= $this->element('Layout/default/menu/menu') ?>
                </nav>
            </div>
        </div>
    </div>
    <div class="container-fluid hidden-md hidden-lg">
        <div class="row">

            <div class="col-sm-6 col-xs-6">
                <?=
                $this->Html->link(
                    $this->Html->image('logo-cake.png', ['alt' => __('Home'), 'fullBase' => true]),
                    '/',
                    ['escape' => false, 'class' => 'logo-cake']
                ); ?>
            </div>
            <div class="col-sm-6 col-xs-6">
                <div class="navbar-right">
                    <button class="btn-menu" data-toggle="modal" data-target="#menumodal"><i
                            class="toggle-modal icon_menu"></i></button>
                </div>
            </div>
        </div>
    </div>
</header>
<?= $this->element('Layout/default/menu/mobile_menu') ?>
