<div class="infobox">

	<h3>Missing a package from Github?</h3>
	<p>Let us know about it!</p>
	<?php echo $this->Form->create('Package', array(
		'inputDefaults' => array('label' => false)
	)); ?>
		<?php echo $this->Form->input('Package.username', array('placeholder' => 'Username')); ?>
		<?php echo $this->Form->input('Package.repository', array('placeholder' => 'Repository')); ?>
		<?php echo $this->Form->submit('Suggest!'); ?>
	<?php echo $this->Form->end(); ?>

</div>