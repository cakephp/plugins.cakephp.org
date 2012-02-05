<?php $this->set('_bodyClass', $_bodyClass . ' page-form'); ?>
<h2>Suggest a New Package for CakePackages</h2>

<?php echo $this->Session->flash(); ?>

<article>
	<p class="description">Use this form to suggest new packages from GitHub:</p>

	<?php
		echo $this->Form->create('Package', array(
			'class' => 'PackageSuggestForm',
			'url' => array('controller' => 'packages', 'action' => 'suggest'),
			'inputDefaults' => array('label' => false),
		));

		echo $this->Form->input('Package.github', array(
			'class' => 'github',
			'placeholder' => __('github repository url'),
		));

		echo $this->Form->button(__('Suggest!'), array(
			'class' => 'button solid-green',
			'div' => false,
		));
		echo '<br />';

		echo $this->Form->end();
	?>
</article>