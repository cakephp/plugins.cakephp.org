<?php $this->Html->h2(sprintf(__('Edit %s', true), $this->Form->value('Maintainer.username')));?>
<?php echo $this->Form->create('Maintainer');?>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('alias');
		echo $this->Form->input('name');
		echo $this->Form->input('url');
		echo $this->Form->input('twitter_username');
	?>
<?php echo $this->Form->end(__('Submit', true));?>