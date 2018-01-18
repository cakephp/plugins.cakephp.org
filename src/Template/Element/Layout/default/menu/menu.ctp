<ul class="menu">
    <li class="toggle-menu"><i class="fa icon_menu"></i></li>
    <li class="first">
        <?=
        $this->Html->link(
            $this->Html->tag('i', '', ['class' => 'fa fa-menu fa-chevron-down']) . __('Documentation'),
            '#',
            ['escape' => false]
        );
        ?>
        <ul class="submenu">
            <?= $this->App->menuItems($this->Menu->documentationItems()); ?>
        </ul>
    </li>
    <li>
        <?=
        $this->Html->link(
            __('Business Solutions'),
            'https://cakephp.org/pages/business-solutions',
            ['escape' => false]
        );?>
    </li>
    <li>
        <?=
        $this->Html->link(
            __('Showcase'),
            'https://cakephp.org/showcase',
            ['escape' => false]
        );?>
    </li>
    <li>
        <?=
        $this->Html->link(
            $this->Html->tag('i', '', ['class' => 'fa fa-menu fa-chevron-down']) . __('Community'),
            '#',
            ['escape' => false]
        );
        ?>
        <div class="megamenu full megamenu2 full2">
            <div class="row">
                <div class="col-6 pl30">
                    <ul class="megamenu-list">
                        <li class="menu-title main-title">
                            <?= $this->Html->link(
                                $this->Html->tag('i', '', ['class' => 'fa fa-menu-title fa-users']) . __('Community'),
                                '#',
                                ['escape' => false]
                            ) ?>
                        </li>
                        <?= $this->App->menuItems($this->Menu->communityItems()); ?>
                    </ul>
                </div>
                <div class="col-6 pl50">
                    <ul class="megamenu-list">
                        <li class="menu-title main-title">
                            <?=
                            $this->Html->link(
                                $this->Html->tag('i', '', ['class' => 'fa fa-menu-title fa-comments-o']) . __('Help & Support'),
                                '#',
                                ['escape' => false]
                            ) ?>
                        </li>
                        <?= $this->App->menuItems($this->Menu->helpAndSupportItems()); ?>
                    </ul>
                </div>
            </div>
        </div>
    </li>

    <?php if ($this->request->session()->check('Auth.User')) : ?>
        <li>
            <?= $this->Html->link(
                $this->Html->tag('i', '', ['class' => 'fa fa-logout']) . __('Logout'),
                ['prefix' => false, 'plugin' => 'Users', 'controller' => 'Users', 'action' => 'logout'],
                ['escape' => false]
            );
            ?>
        </li>
    <?php endif; ?>
</ul>
