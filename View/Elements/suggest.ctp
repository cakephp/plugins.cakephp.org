<div class="infobox">

	<h3>Missing a package from Github?</h3>
	<p>Let us know about it!</p>
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

		echo $this->Form->end();
	?>

</div>