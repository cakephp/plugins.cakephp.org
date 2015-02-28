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

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

	<?php if ($this->theme == 'Csf' && CakePlugin::loaded('Csfnavbar')) : ?>
		<?php echo $this->AssetCompress->css('csfbootstrap'); ?>
	<?php else : ?>
		<?php echo $this->AssetCompress->css('bootstrap'); ?>
	<?php endif; ?>

	<!--[if lt IE 9]>
		<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<!--[if IE]>
		<style type="text/css">
			.clearfix { zoom: 1; }
		</style>
	<![endif]-->
	<?php if (Configure::read('Environment.name') == 'production') : ?>
	<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-743287-12']);
		_gaq.push(['_trackPageview']);

		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
	</script>
	<?php endif; ?>
</head>

<body class="<?php echo $_bodyClass; ?>" id="<?php echo $_bodyId; ?>">

	<?php if ($this->theme == 'Csf' && CakePlugin::loaded('Csfnavbar')) : ?>
		<?php echo $this->element('Csfnavbar.navbar'); ?>
	<?php endif; ?>

	<div class="header">
		<div class="container">
			<h1 class="h1">
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
		</div>
	</div>

	<div class="wrapper">
		<div class="content container">
			<?php
				$isAdmin = $this->request->param('admin');
				$isMaintainers = $this->request->param('controller') == 'maintainers';
				$isUsers = $this->request->param('controller') == 'users';
				$isLogin = $this->request->param('action') == 'login';
				if (!$isMaintainers && !$isAdmin && !($isUsers && $isLogin)) {
					echo $this->element('categories', array(), array('cache' => Configure::read('debug') == 0));
				}

				echo $this->Session->flash();
				echo $content_for_layout;
			?>
		</div>
		<div class="push"></div>
	</div>

	<div class="footer">
		<div class="container">
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

		<?php if (Configure::read('Environment.name') == 'production') : ?>
			$('.github-external').click(function(e) {
				e = e || window.event;
				target = e.target || e.srcElement;
				_gaq.push(['_trackEvent', 'click', 'github-external', $(target).attr('package-name')]);
			});
			$('.blog-external').click(function(e) {
				e = e || window.event;
				target = e.target || e.srcElement;
				_gaq.push(['_trackEvent', 'click', 'blog-external', $(target).text()]);
			});
			$('.video-external').click(function(e) {
				e = e || window.event;
				target = e.target || e.srcElement;
				_gaq.push(['_trackEvent', 'click', 'video-external', $(target).text()]);
			});
			$('.download-link').click(function(e) {
				e = e || window.event;
				target = e.target || e.srcElement;
				_gaq.push(['_trackEvent', 'click', 'download-link', $(target).attr('package-id')]);
			});
		<?php endif; ?>

	</script>
	<?php echo $scripts_for_layout; ?>
	<?php echo $this->AssetCompress->script('default'); ?>
</body>

</html>
