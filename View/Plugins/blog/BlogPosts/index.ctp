<h2>
	CakePackages Blog
</h2>

<?php foreach ($blogPosts as $blogPost) : ?>
	<article>
		<h3><?php echo $this->Html->link($blogPost['BlogPost']['title'], array(
			'plugin' => 'blog', 'controller' => 'blog_posts', 'action' => 'view', $blogPost['BlogPost']['slug'])); ?></h3>
		<?php echo $this->Textile->output($blogPost['BlogPost']['content']); ?>
	</article>
<?php endforeach; ?>