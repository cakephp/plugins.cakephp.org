<!DOCTYPE html>
<head>
	<?php echo $this->Sham->out('charset'); ?>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<?php echo $this->Sham->out(null, array('skip' => array('charset'))); ?>

	<?php $baseUrl = Router::url('/', true); ?>
	<link rel="shortcut icon" href="<?php echo $baseUrl; ?>favicon.ico" />
	<!-- For iPhone 4 with high-resolution Retina display: -->
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo $baseUrl; ?>apple-touch-icon-114x114-precomposed.png">
	<!-- For first-generation iPad: -->
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo $baseUrl; ?>apple-touch-icon-72x72-precomposed.png">
	<!-- For non-Retina iPhone, iPod Touch, and Android 2.1+ devices: -->
	<link rel="apple-touch-icon-precomposed" href="<?php echo $baseUrl; ?>apple-touch-icon-precomposed.png">

	<?php
		if ($this->theme == 'Csf') {
			if (CakePlugin::loaded('Csfnavbar')) {
				echo $this->AssetCompress->css('csftheme');
			} else {
				echo $this->AssetCompress->css('theme');
			}
		} else {
			if (CakePlugin::loaded('Csfnavbar')) {
				echo $this->AssetCompress->css('csfdefault');
			} else {
				echo $this->AssetCompress->css('default');
			}
		}
	?>

	<!--[if lt IE 9]>
		<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<!--[if IE]>
		<style type="text/css">
			.clearfix { zoom: 1; }
		</style>
	<![endif]-->
	<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-743287-12']);
		_gaq.push(['_trackPageview']);

		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();

		var RecaptchaOptions = {
			theme : 'clean'
		};
	</script>
</head>

<body class="<?php echo $_bodyClass; ?>" id="<?php echo $_bodyId; ?>">

	<?php if ($this->theme == 'Csf' && CakePlugin::loaded('Csfnavbar')) : ?>
		<?php echo $this->element('Csfnavbar.navbar'); ?>
	<?php endif; ?>

	<div class="wrapper">
		<header>
			<div class="container">
				<nav class="left-nav">
					<ul>
						<li>
							<h1>
								<?php
									$siteTitle = Configure::read('Settings.SiteTitle');
									if (!$siteTitle) $siteTitle = __('Package Indexer');
									if ($this->theme == 'Csf') {
										echo $this->Html->link($this->Html->image('cake-logo.png', array(
											'alt' => $siteTitle, 'width' => '70'
										)) . $siteTitle, '/', array('escape' => false));
									} else {
										echo $this->Html->link($siteTitle, '/');
									}
								?>
							</h1>
						</li>
					</ul>
				</nav>
				<nav class="right-nav">
					<ul>
						<li>
							<?php echo $this->Html->link('Categories', array('controller' => 'packages', 'action' => 'categories')); ?>
						</li>
						<li>
							<?php echo $this->Html->link('Packages', array('controller' => 'packages', 'action' => 'index')); ?>
						</li>
						<li>
							<?php echo $this->Html->link('Suggest', array('controller' => 'packages', 'action' => 'suggest')); ?>
						</li>
						<?php if ($this->Session->read('Auth.User')) : ?>
							<li>
								<?php echo $this->Html->link('Logout', array('controller' => 'users', 'action' => 'logout')); ?>
							</li>
						<?php endif; ?>
					</ul>
				</nav>
			</div>
		</header>

		<div class="header-bottom"></div>

		<div class="content container">
			<?php echo $this->Session->flash(); ?>
			<?php echo $content_for_layout; ?>
		</div>
		<div class="push"></div>
	</div>

	<footer>
		<div class="container">
			<div class="copyright">
				<a href="http://www.cakephp.org/" target="_blank">
					<img src="/img/cake.power.gif" alt="CakePHP: the rapid development php framework" border="0" height="13" width="98">
				</a><br />
				<?php
					echo sprintf(
						__('Powered by %s'),
						$this->Html->link('CakePackages', 'http://github.com/cakephp/cakepackages')
					) .
					' &copy; 2009 - ' . date('Y') . ' ' .
					$this->Html->link('Jose Diaz Gonzalez', 'http://josediazgonzalez.com', array('target' => '_blank')) .
					'<br />CakePHP Package Indexer &copy; 2011 - ' . date('Y') . ' ' .
					$this->Html->link('Cake Software Foundation, Inc.', 'http://cakefoundation.org', array('target' => '_blank'));
				?>
			</div>
		</div>
	</footer>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script>
		!window.jQuery &&
		document.write(unescape('%3Cscript src="<?php echo $this->Html->url("/js/jquery-1.7.1.min.js") ?>"%3E%3C/script%3E'));
	</script>
	<script type="text/javascript">
		var App = App || {};
		App.basePath = "<?php echo $this->webroot; ?>";
		<?php
			$jsUser = array();
			$userData = $this->Session->read('Auth.User');

			if (!empty($userData['username'])) {
				$jsUser['username'] = $userData['username'];
			}
			if (!empty($userData['slug'])) {
				$jsUser['slug'] = $userData['slug'];
			}
		?>
		App.user = <?php echo empty($jsUser) ? '{}' : json_encode($jsUser); ?>;

		<?php if (!empty($disqus)) : ?>
			var disqus_developer = <?php echo Configure::read('Disqus.disqus_developer') ?>;
			<?php foreach ($disqus as $k => $v) : ?>
				<?php printf("var %s = '%s';\n", $k, $v) ?>
			<?php endforeach; ?>

			(function() {
				var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
				dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
				(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
			})();
		<?php endif; ?>
	</script>
	<?php echo $scripts_for_layout; ?>
	<?php echo $this->AssetCompress->script('default'); ?>
</body>

</html>