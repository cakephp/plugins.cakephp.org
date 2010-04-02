<?php echo $this->Html->h2(__('Reset Password', true)); ?>
<?php echo $this->Session->flash(); ?>
<?php echo $this->Form->create('User');?>
	<?php echo $this->Form->input('User.password',
			array('label' => __('New Password', true),
				'placeholder' => __('your new password', true))); ?>
	<?php echo $this->Form->submit(__('Change Password', true),
			array('div' => 'submit cancel')); ?> or 
	<?php echo $this->Clearance->link(__('login', true),
			array('controller' => 'users', 'action' => 'login'),
			array('class' => 'cancel-action')); ?>
<?php echo $this->Form->end(); ?>