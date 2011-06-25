<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js ie6" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
	<head>
		<?php echo $this->Sham->out('charset'); ?>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<?php echo $this->Sham->out(null, array('skip' => array('charset'))); ?>
		<?php echo $this->Html->css(array('style', 'uniform.default')); ?>
		<script type="text/javascript">
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-8668344-5']);
			_gaq.push(['_trackPageview']);
		</script>
	</head>
	<body class="<?php echo $this->params['controller'] . '-c ' . $this->params['action'] . '-a'; ?>">
		<div class="wrapper">
			<header>
				<h1 class="logo"><?php echo $this->Html->link('CakePackages', '/'); ?></h1>
				<nav class="navigation">
					<ul>
						<li><?php echo $this->Html->link('Browse Packages', array('plugin' => null, 'controller' => 'packages', 'action' => 'index')); ?></li>
						<li><?php echo $this->Html->link('Blog', array('plugin' => 'blog', 'controller' => 'blog_posts', 'action' => 'index')); ?></li>
					</ul>
				</nav>
				<nav class="filter">
					<?php echo $this->element('navigation', array('cache' => array('key' => sha1(serialize($this->params['named'])), 'time' => '+1 hour'))); ?>
				</nav>
			</header>
			<section>
				<div class="contents">
					<?php echo $content_for_layout; ?>
				</div>
			</section>
			<footer>
				<ul>
					<li><?php echo $this->Html->link('Open source', array('plugin' => null, 'controller' => 'pages', 'action' => 'display', 'opensource')); ?></li>
					<li><?php echo $this->Html->link('About', array('plugin' => null, 'controller' => 'pages', 'action' => 'display', 'about')); ?></li>
				</ul>
			</footer>
		</div>
		<?php if (Configure::read() == 0 && Authsome::get('group') != 'admin' ) : ?>
		<script type="text/javascript">
			(function() {
				var ga = document.createElement('script');     ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:'   == document.location.protocol ? 'https://ssl'   : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
		</script>
		<?php endif; ?>
		<?php echo $this->Html->script(array('jquery.min', 'jquery.uniform.min')); ?>
		<?php echo $scripts_for_layout; ?>
		<script type="text/javascript">
			jQuery.noConflict();
			jQuery(function() {
				jQuery("select, input:checkbox, input:radio, input:file, .contents input").uniform();
			});
		</script>
	</body>
</body>