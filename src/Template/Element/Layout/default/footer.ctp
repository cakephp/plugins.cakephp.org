<section id="socials" class="back-3 footer-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-5  social text-center">
                <?= $this->element('Layout/default/footer/social_interactions') ?>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-7 social-footer text-center ">
                <?= $this->element('Layout/default/footer/social_links') ?>
            </div>

        </div>
    </div>
</section>

<footer id="footer" class="footer-wrapper">
    <div class="container">
        <div class="row col-p30">
            <div class="col-sm-12 col-md-3 clearfix">
                <div class="footer-widget t-footer">
                    <?= $this->element('Layout/default/footer/login_form') ?>
                </div>
            </div>

            <div class="col-sm-12 col-md-9">
                <?= $this->element('Layout/default/footer/menu') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-center mt30">
                <a href="https://cakefoundation.org" target="_blank"><span class="glyph_range icon-expertise buss-icon-8">u</span></a>
                <p class="copyright">&copy; <?= __('Copyright 2005-2016 Cake Software Foundation, Inc. All rights reserved. {0}.',
                            $this->Html->link(__('Designs by Ibaldo'), ['prefix' => false, 'plugin' => false, 'controller' => 'pages', 'action' => 'display', 'rebranding'], ['class' => 'ibaldo']))?></p>
            </div>
        </div>
    </div>
</footer>
