<div class="infobox">

	<h3>Missing a package from Github?</h3>
	<p>Let us know about it!</p>
	<?php echo $this->Form->create('Package', array(
		'class' => 'PackageSuggestForm',
		'url' => array('controller' => 'packages', 'action' => 'suggest'),
		'inputDefaults' => array('label' => false)
	)); ?>
		<?php echo $this->Form->input('Package.username', array('placeholder' => 'github username')); ?>
		<?php echo $this->Form->input('Package.repository', array('placeholder' => 'repository')); ?>
		<?php
			echo $this->Form->button(__('Suggest!'), array(
				'class' => 'button solid-green',
				'div' => false,
			));
		?>
	<?php echo $this->Form->end(); ?>

</div>