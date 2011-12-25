<!DOCTYPE html>
<head>
	<?php echo $this->Sham->out('charset'); ?>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<?php echo $this->Sham->out(null, array('skip' => array('charset'))); ?>
	<?php echo $this->AssetCompress->css('default'); ?>
	<title>
		<?php echo __('CakePHP Packages -') . $title_for_layout; ?>
	</title>
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
		_gaq.push(['_setAccount', 'UA-8668344-5']);
		_gaq.push(['_trackPageview']);
		var RecaptchaOptions = {
			theme : 'clean'
		};
	</script>

</head>

<body class="<?php echo $this->request->params['controller'] . '-' . $this->request->params['action'] ?>">


	<div class="wrapper">
		<?php echo $this->element('new/header'); ?>
		<div class="content container">
			<?php echo $this->Session->flash(); ?>
			<?php echo $content_for_layout; ?>
		</div>
		<div class="push"></div>
	</div>

	<?php echo $this->element('new/footer'); ?>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
	<script>
		!window.jQuery &&
		document.write(unescape('%3Cscript src="<?php $this->Html->url("/js/jquery-1.7.1.min.js") ?>"%3E%3C/script%3E'));
	</script>
	<?php echo $this->AssetCompress->script('default'); ?>
	<script type="text/javascript">
		var App = App || {};
		App.basePath = "<?php echo $this->webroot; ?>";
		App.user = <?php echo empty($userData) ? '{}' : json_encode(array('username' => $userData['username'], 'slug' =>  $userData['slug'])); ?>;

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
	<?php echo $this->Js->writeBuffer(); ?>
</body>

</html>