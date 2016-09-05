<div class="modal fade" id="menumodal" tabindex="-1" aria-labelledby="modal-label-3" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-content-menu">
            <div class="modal-header modal-header-menu">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row mb20">
                    <div class="col-xs-10 col-xs-offset-1">
                        <div class="panel-group" id="toggle">

                            <div class="panel">
                                <div class="panel-heading p0">
                                    <h4 class="panel-title title-responsivo-menu">
                                        <a data-toggle="collapse" href="#Documentationmodal">
                                            <i class="fa fa-caret-down upside-down"></i><?= __('Documentation') ?>
                                        </a>
                                    </h4>
                                </div>
                                <div id="Documentationmodal" class="panel-collapse collapse">
                                    <div class="panel-body sidebar-menu-body">
                                        <ul>
                                            <?= $this->App->menuItems($this->Menu->documentationItems()); ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="panel">
                                <div class="panel-heading p0">
                                    <h4 class="panel-title title-responsivo-menu">
                                        <a href="/pages/business-solutions">
                                            <i class="fa fa-caret-right upside-stop"></i><?= __('Business Solutions') ?>
                                        </a>
                                    </h4>
                                </div>
                            </div>

                            <div class="panel">
                                <div class="panel-heading p0">
                                    <h4 class="panel-title title-responsivo-menu">
                                        <a href="/showcase">
                                            <i class="fa fa-caret-right upside-stop"></i><?= __('Showcase') ?>
                                        </a>
                                    </h4>
                                </div>
                            </div>

                            <div class="panel">
                                <div class="panel-heading p0">
                                    <h4 class="panel-title title-responsivo-menu">
                                        <a data-toggle="collapse" href="#communitymodal">
                                            <i class="fa fa-caret-down upside-down"></i><?= __('Community') ?>
                                        </a>
                                    </h4>
                                </div>
                                <div id="communitymodal" class="panel-collapse collapse">
                                    <div class="panel-body sidebar-menu-body tarja-menu">
                                        <ul>
                                            <li class="menu-title"><i class="fa fa-menu-title fa-comments-o"></i><?= __('Help &
                                                Support') ?>
                                            </li>
                                            <?= $this->App->menuItems($this->Menu->helpAndSupportItems()); ?>
                                          </ul>
                                        <ul>
                                            <li class="menu-title"><i class="fa fa-menu-title fa-users"></i><?= __('Community') ?>
                                            </li>
                                            <?= $this->App->menuItems($this->Menu->communityItems()); ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
