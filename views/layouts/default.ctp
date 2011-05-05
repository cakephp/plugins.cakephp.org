<!DOCTYPE html>
<html lang="en"> 
  <head>
    <?php echo $this->Html->charset('utf-8'); ?>
    <title><?php echo sprintf("%s | %s", $title_for_layout, __('CakePackages | the cakephp package repository', true)); ?></title>
    <?php
      $this->AssetCompress->css('html5/01_reset.css');
      $this->AssetCompress->css('html5/02_overrides.css');
      $this->AssetCompress->css('html5/03_positioning.css');
      $this->AssetCompress->css('html5/04_forms.css');
      $this->AssetCompress->css('html5/05_icons.css');
      $this->AssetCompress->css('html5/06_tipsy.css');
      $this->AssetCompress->css('html5/07_search.css');
      $this->AssetCompress->css('html5/08_home.css');
      $this->AssetCompress->css('html5/09_package.css');
      $this->AssetCompress->css('html5/10_maintainer.css');
      $this->AssetCompress->css('html5/11_indexes.css');
      $this->AssetCompress->css('html5/12_pages.css');
      echo $this->AssetCompress->includeAssets();
    ?>
    <!--[if lt IE 9]>
      <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <?php
      $this->AssetCompress->script('jquery-1.5.1.min.js');
      $this->AssetCompress->script('jquery-ui-1.8.10.custom.min.js');
      $this->AssetCompress->script('autocomplete.js');
      $this->AssetCompress->script('tipsy-1.0.0a.js');
      $this->AssetCompress->script('custom.js');
    ?>
    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-8668344-5']);
        _gaq.push(['_trackPageview']);
      </script>
  </head>
  <body id="<?php echo $this->params['controller'] . '_' . $this->params['action']; ?>">
    <header>
      <div class="header">
        <h1><?php echo $this->Html->link(__('CakePackages', true), array(
          'plugin' => null, 'controller' => 'packages', 'action' => 'home')); ?></h1>
        <h2><?php echo $h2_for_layout; ?></h2>
        <h3><?php if (isset($h3_for_layout)) echo $h3_for_layout; ?></h3>
      </div>
      <?php echo $this->element('search'); ?>
    </header>
    <div class="clear"></div>

    <?php if ($this->params['action'] == 'home') echo $this->element('navigation'); ?>

    <div id="main" class="<?php echo $this->params['controller'] . '_' . $this->params['action']; ?>">
      <?php if ($this->Session->check('flash')) : ?>
        <p><?php echo $this->Session->flash(); ?></p>
      <?php endif; ?>
      <?php echo $content_for_layout; ?>
    </div>

    <footer>
      <?php echo $this->Html->link('about', array(
        'plugin' => null, 'controller' => 'pages', 'action' => 'display', 'about')); ?> &#183; 
      <?php echo $this->Html->link('opensource', array(
        'plugin' => null, 'controller' => 'pages', 'action' => 'display', 'opensource')); ?> &#183; 
      <?php echo $this->Html->link('blog', array(
        'plugin' => 'blog', 'controller' => 'blog_posts', 'action' => 'index')); ?> &#183; 
      <?php echo $this->Html->link('twitter',
        'http://twitter.com/cakepackages',
        array('target' => '_blank')); ?> &#183; 
      <?php echo $this->Html->link('github',
        'http://github.com/josegonzalez/cakepackages',
        array('target' => '_blank')); ?>
      <br />
      <?php echo $this->Html->link(
          $this->Html->image('cake.power.gif', array(
              'alt'=> __('CakePHP: the rapid development php framework', true),
              'border' => '0',
              'height' => 13,
              'width' => 93,
          )),
          'http://www.cakephp.org/',
          array('target' => '_blank', 'escape' => false)
        );
      ?>
    </footer>
    <?php
      echo $this->AssetCompress->includeJs();
    ?>
    <script type="text/javascript">jQuery.noConflict();</script>
    <?php
      echo $scripts_for_layout;
    ?>
    <?php if (Configure::read() == 0 && Authsome::get('group') != 'admin' ) : ?>
        <script type="text/javascript">
            (function() {
                var ga = document.createElement('script');     ga.type = 'text/javascript'; ga.async = true;
                ga.src = ('https:'   == document.location.protocol ? 'https://ssl'   : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
            })();
        </script>
    <?php endif; ?>
  </body>
</html>