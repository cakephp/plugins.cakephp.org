<?php echo $this->Html->h2(__('Posts', true)); ?>
<?php foreach ($blogPosts as $blogPost) : ?>
	<h3><?php echo $this->Html->link($blogPost['BlogPost']['title'], array(
		'plugin' => 'blog', 'controller' => 'blog_posts', 'action' => 'view', $blogPost['BlogPost']['slug'])); ?></h3>
		<?php echo $this->Textile->output($blogPost['BlogPost']['content']); ?>
<?php endforeach; ?>