<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php echo $this->Html->charset(); ?>
		<title>
			<?php echo "{$title_for_layout}" ; ?>
			<?php __('CakePackages | the cakephp package repository'); ?>
		</title>
		<?php echo $this->Html->meta('icon'); ?>
		<?php echo $this->Html->css(array('960', 'default')); ?>
		<?php echo $scripts_for_layout; ?>
	</head>
	<body>
		<div id="container" class="container_6">
			<?php echo $this->element('header'); ?>
			<div class="grid_6">
				<div class="content">
					<?php echo $h2_for_layout; ?>
					<?php echo $this->Session->flash(); ?>
					<?php echo $content_for_layout; ?>
				</div>
			</div>
			<div class="clear"></div>
			<?php echo $this->element('footer'); ?>
		</div>
		<script type="text/javascript">
			var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
			document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
		</script>
		<script type="text/javascript">
			try {
				var pageTracker = _gat._getTracker("UA-8668344-5");
				pageTracker._trackPageview();
			} catch(err) {}
		</script>
	</body>
</html>