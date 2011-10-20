<!DOCTYPE html>
<head>
	<?php echo $this->Sham->out('charset'); ?>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<?php echo $this->Sham->out(null, array('skip' => array('charset'))); ?>
	<?php echo $this->AssetCompress->css('default'); ?>
	<title>
		<?php __('CakePHP Packages -'); ?>
		<?php echo $title_for_layout; ?>
	</title>
	<!--[if lt IE 9]>
		<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<script type="text/javascript">
		var RecaptchaOptions = {
			theme : 'clean'
		};
	</script>

</head>

<body>
	<div id="messages-container"></div>
	<?php echo $this->element('navbar', array('plugin' => 'csfnavbar')); ?>
	<?php echo $this->element('layout/header'); ?>
	<div id="container-wrapper">
		<div id="container">
			<div id="content">
				<div class="inner-container">
					<?php echo $this->Session->flash(); ?>
					<?php echo $content_for_layout; ?>
				</div>
			</div>
		</div>
	</div>
	<?php echo $this->element('layout/footer'); ?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.6/jquery-ui.min.js"></script>
	<script>
		!window.jQuery &&
		document.write(unescape('%3Cscript src="<?php $this->Html->url("/js/jquery-1.4.2.min.js") ?>"%3E%3C/script%3E'));
	</script>
	<?php echo $this->Html->script(array('core')); ?>
	<script>
		var App = App || {};
		App.basePath = "<?php echo $this->webroot; ?>";
		App.user = <?php echo empty($userData) ? '{}' : json_encode(array('username' => $userData['username'], 'slug' =>  $userData['slug'])); ?>;
	</script>
	<?php echo $this->Js->writeBuffer(); ?>
</body>

</html>