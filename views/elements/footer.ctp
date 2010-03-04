<div id="footer" class="grid_6 clearfix">
	<?php echo $this->Html->link('packages', array(
		'plugin' => null, 'controller' => 'packages', 'action' => 'index')); ?> | 
	<?php echo $this->Html->link('about', array(
		'plugin' => null, 'controller' => 'pages', 'action' => 'display', 'about')); ?> | 
	<?php echo $this->Html->link('blog', array(
		'plugin' => 'blog', 'controller' => 'blog_posts', 'action' => 'index')); ?> | 
	<?php echo $this->Html->link('twitter', 'http://twitter.com/cakepackages'); ?> | 
	<?php echo $this->Html->link('github', 'http://github.com/josegonzalez/cakepackages'); ?>
	<br />
	<?php echo $this->Html->link(
			$this->Html->image('cake.power.gif', array('alt'=> __('CakePHP: the rapid development php framework', true), 'border' => '0')),
			'http://www.cakephp.org/',
			array('target' => '_blank', 'escape' => false)
		);
	?>
</div>