<h2>
	<?php echo $blogPost['BlogPost']['title']; ?>
</h2>

<article>
	<?php echo $this->Textile->output($blogPost['BlogPost']['content']); ?>
</article>