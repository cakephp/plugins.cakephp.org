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
		<?php echo $this->Html->script(array('prototype', 'scriptaculous'), array('inline' => false)); ?>
		<?php echo $scripts_for_layout; ?>
	</head>
	<body>
		<div id="container" class="container_6">
			<div id="header" class="grid_6 clearfix">
				<h1>
					<?php echo $this->Clearance->link(__('CakePackages', true), array(
						'plugin' => null, 'controller' => 'packages', 'action' => 'home')); ?>
				</h1>
			</div>
			<div id="navigation" class="grid_6 clearfix">
				<?php echo $this->Clearance->link('packages', array(
					'plugin' => null, 'controller' => 'packages', 'action' => 'index')); ?> &#183; 
				<?php echo $this->Clearance->link('maintainers', array(
					'plugin' => null, 'controller' => 'maintainers', 'action' => 'index')); ?> &#183; 
				<?php echo $this->Clearance->link('search', 	array(
					'plugin' => 'searchable', 'controller' => 'search_indexes', 'action' => 'index', 'type' => 'Package')); ?>
			</div>
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
			<div id="footer" class="grid_6 clearfix">
				<?php echo $this->Clearance->link('about', array(
					'plugin' => null, 'controller' => 'pages', 'action' => 'display', 'about')); ?> &#183; 
				<?php echo $this->Clearance->link('blog', array(
					'plugin' => 'blog', 'controller' => 'blog_posts', 'action' => 'index')); ?> &#183; 
				<?php echo $this->Clearance->link('twitter', 'http://twitter.com/cakepackages'); ?> &#183; 
				<?php echo $this->Clearance->link('github', 'http://github.com/josegonzalez/cakepackages'); ?>
				<br />
				<?php echo $this->Clearance->link(
						$this->Html->image('cake.power.gif', array('alt'=> __('CakePHP: the rapid development php framework', true), 'border' => '0')),
						'http://www.cakephp.org/',
						array('target' => '_blank', 'escape' => false)
					);
				?>
			</div>
		</div>
		<?php echo $this->element('analytics'); ?>
	</body>
</html>