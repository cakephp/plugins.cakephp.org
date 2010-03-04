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
			<div class="grid_6 alternate-content">
				<div class="grid_1 alpha"><p></p></div>
				<div class="grid_4 content-makeup-vertical">
					<?php echo $h2_for_layout; ?>
					<?php echo $this->Session->flash(); ?>
					<?php echo $content_for_layout; ?>
				</div>
				<div class="grid_1 omega"><p></p></div>
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
			<?php echo $this->element('footer'); ?>
		</div>
		<?php echo $this->element('analytics'); ?>
	</body>
</html>