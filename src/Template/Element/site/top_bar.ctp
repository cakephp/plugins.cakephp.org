<section id="sub">
    <div class="container-fluid ">
        <div class="row ">
            <div class="col-md-12 back-book clearfix">
                <div class="col-md-6 col-md-offset-1 text-center t-cook-nav p0 hidden-sm hidden-xs">
                    <h2>
                        <a href="/">
                            <span class="glyph_range icon-submenu icon-submenu-cook">B</span>
                            CakePHP <strong>Plugins</strong>
                        </a>
                    </h2>
                </div>

                <?php if (empty($searchForm)) $searchForm = null; ?>
                <div class="col-md-5 hidden-sm">
                    <?php echo $this->Form->create($searchForm, [
                        'class' => 'search',
                        'method' => 'post',
                        'url' => ['plugin' => null, 'controller' => 'packages', 'action' => 'index'],
                    ]); ?>
                        <div class="col-xs-11 col-md-10 p0">
                            <span class="twitter-typeahead" style="position: relative; display: inline-block;">
                                <?php
                                    echo $this->Form->input('query', [
                                        'before' => false,
                                        'after' => false,
                                        'class' => 'form-control form-cook tt-input',
                                        'autocomplete' => 'off',
                                        'type' => 'search',
                                        'name' => 'query',
                                        'size' => '18',
                                        'placeholder' => __('Plugins Search (ex: debug has:component)'),
                                        'spellcheck' => 'false',
                                        'dir' => 'auto',
                                        'style' => 'position: relative; vertical-align: top;',

                                        'div' => false,
                                        'label' => false,
                                    ]);
                                ?>
                            </span>
                        </div>
                        <div class="col-xs-1 col-md-2 p0 search-cook">
                            <button type="submit">
                                <span class="glyph_range icon-submenu icon-submenu-cook">A</span>
                            </button>
                        </div>
                    <?php echo $this->Form->end();?>

                </div>


            </div>
        </div>
    </div>
</section>
