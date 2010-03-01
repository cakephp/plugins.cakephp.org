<div class="packages form">
<?php echo $this->Form->create('Package');?>
	<fieldset>
 		<legend><?php printf(__('Add %s', true), __('Package', true)); ?></legend>
	<?php
		echo $this->Form->input('maintainer_id');
		echo $this->Form->input('name');
		echo $this->Form->input('bakery_article');
		echo $this->Form->input('package_url');
		echo $this->Form->input('homepage');
		echo $this->Form->input('description');
		echo $this->Form->input('tags');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>