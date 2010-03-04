<?php echo $this->Html->h2(__('Forgot password', true));  ?>
<?php echo $this->Session->flash(); ?>
<?php echo $this->Form->create('User');?>
<?php echo $this->Form->input('User.email',
		array('label' => __('Email', true),
			'placeholder' => 'your email address')); ?>
<?php echo $this->Form->submit(__('Request Password Reset', true),
		array('div' => 'submit cancel')); ?> or 
<?php echo $this->Html->link(__('login', true),
			array('controller' => 'users', 'action' => 'login'),
			array('class' => 'cancel-action')); ?>
<?php echo $this->Form->end(); ?>
