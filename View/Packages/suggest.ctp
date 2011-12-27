<?php $this->set('bodyClass', $bodyClass . ' page-form'); ?>
<h2>Suggest a New Package for CakePackages</h2>

<?php echo $this->Session->flash(); ?>

<article>
	<p class="description">Use this form to suggest new packages from GitHub:</p>

	<?php
		echo $this->Form->create('Package');

		echo $this->Form->input('Package.username', array(
			'label' => __('Github Username'),
			'placeholder' => __('username'),
			'type' => 'text'
		));
		echo $this->Form->input('Package.repository', array(
			'label' => __('Github Repository name'),
			'placeholder' => __('repository')
		));

		echo $this->Form->submit(__('Submit Github Repository'), array('div' => 'submit forgot'));
		echo '<br />';

		echo $this->Form->end();
	?>
</article>