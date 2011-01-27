<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php echo $this->Html->charset(); ?>
		<title>
			<?php echo "{$title_for_layout}" ; ?>
			<?php __('CakePackages | the cakephp package repository'); ?>
		</title>
		<?php echo $this->Html->meta('icon'); ?>
		<?php echo $this->Html->css(array('style', '960')); ?>
		<?php echo $this->Html->script(array('prototype', 'scriptaculous')); ?>
		<?php echo $scripts_for_layout; ?>
	</head>
	<body class="<?php echo $this->params['controller']; ?> <?php echo $this->params['action']; ?>">
		<div class="container_12">
			<div class="grid_12">
				<div class="grid_6 alpha">
					<h1>
						<?php echo $this->Html->link(__('CakePackages', true), array(
							'plugin' => null, 'controller' => 'packages', 'action' => 'home')); ?>
					</h1>
					<h2><?php echo $h2_for_layout; ?></h2>
					<h3><?php if (isset($h3_for_layout)) echo $h3_for_layout; ?></h3>
				</div>
				<div class="grid_6 omega">
					<?php echo $this->element('search'); ?>
				</div>
			</div>
			<div class="clear"></div>
			<div class="grid_12">
				<p><?php echo $this->Session->flash(); ?></p>
				<?php echo $content_for_layout; ?>
			</div>
			<!-- end .grid_12 -->
			<div class="clear"></div>
			<div class="footer prefix_3 grid_6 suffix_3">
				<?php echo $this->Html->link('about', array(
					'plugin' => null, 'controller' => 'pages', 'action' => 'display', 'about')); ?> &#183; 
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
						$this->Html->image('cake.power.gif', array('alt'=> __('CakePHP: the rapid development php framework', true), 'border' => '0')),
						'http://www.cakephp.org/',
						array('target' => '_blank', 'escape' => false)
					);
				?>
			</div>
			<div class="clear"></div>
		</div>
		<?php echo $this->element('analytics'); ?>
	</body>
</html>