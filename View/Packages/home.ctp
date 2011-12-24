<section class="contribute">
	<div class="bubble-top bubble-border">
		<h2 class="header">Use and Share Open Source CakePHP Code</h2>
	</div>
	<div class="bubble-bottom clearfix">
		<article>
			Signup
		</article>
		<article>
			Create a Package
		</article>
		<article>
			Install Plugins
		</article>
		<article>
			Follow Repositories
		</article>
	</div>
</section>

<section class="packages">
	<h2><?php echo __('Latest CakePHP code'); ?></h2>
	<?php foreach ($packages as $package) : ?>
		<article>
			<?php echo $this->element('new/preview', array(
				'package' => $package['Package'],
				'maintainer' => $package['Maintainer'],
			)); ?>
		</article>
	<?php endforeach; ?>
</section>