<?php $this->Html->h2(sprintf(__('Edit %s', true), $this->Form->value('Package.name')));?>
<?php echo $this->Form->create('Package');?>
	<?php
		echo $this->Form->input('Package.id');
		echo $this->Form->input('Package.maintainer_id');
		echo $this->Form->input('Package.name');
		echo $this->Form->input('Package.bakery_article');
		echo $this->Form->input('Package.homepage');
		echo $this->Form->input('Package.description');
		echo $this->Form->input('Package.tags');
	?>
<?php echo $this->Form->end(__('Submit', true)); ?>