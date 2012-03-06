<?php $this->set('_bodyClass', $_bodyClass . ' page-form'); ?>
<h2>Categories</h2>

<?php echo $this->Session->flash(); ?>

<article>
	<p class="description">We've presorted the available packages for easy consumption. You can use the built-in search form with category filtering to find exactly the package you need.</p>
	<ul>
		<?php foreach ($categories as $slug => $name) : ?>
			<li>
				<?php echo $this->Html->link($name, array(
					'controller' => 'packages',
					'action' => 'index', 
					'?' => array('category' => $slug)
				)); ?>
			</li>
		<?php endforeach; ?>
	</ul>
</article>