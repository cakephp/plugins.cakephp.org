<h2 class="secondary-title">
	<?php echo $blogPost['BlogPost']['title']; ?>
</h2>

<article>
	<?php echo $this->Textile->output($blogPost['BlogPost']['content']); ?>
</article>