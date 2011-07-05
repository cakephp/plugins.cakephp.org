<h2 class="secondary-title">
	Suggest a New Package for CakePackages
</h2>

<?php echo $this->Session->flash(); ?>

<article>
	<p class="description">Use this form to suggest new packages from GitHub:</p>

	<?php echo $this->Form->create('Package'); ?>
		<?php echo $this->Form->input('Package.username',
				array('label' => __('Github Username', true),
					'placeholder' => __('username', true),
					'type' => 'text')); ?>
		<?php echo $this->Form->input('Package.repository',
				array('label' => __('Github Repository name', true),
					'placeholder' => __('repository', true))); ?>
		<?php echo $this->Form->submit(__('Submit Github Repository', true),
				array('div' => 'submit forgot')); ?>
		<br />
	<?php echo $this->Form->end(); ?>
</article>