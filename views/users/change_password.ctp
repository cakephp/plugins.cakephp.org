<?php echo $this->Html->h2(__('Change Password', true)); ?>
<?php echo $this->Session->flash(); ?>
<?php echo $this->Form->create('User'); ?>
	<?php echo $this->Form->input('User.password',
			array('label' => __('Current Password', true))); ?>
	<?php echo $this->Form->input('User.new_password',
			array('div' => 'input password required',
				'label' => __('New Password', true),
				'type' => 'password')); ?>
	<?php echo $this->Form->input('User.new_password_confirm',
			array('div' => 'input password required',
				'label' => __('Confirm New Password', true),
				'type' => 'password')); ?>
	<?php echo $this->Form->submit(__('Change Password', true),
			array('div' => 'submit cancel')); ?> or 
	<?php echo $this->Html->link(__('go to dashboard', true),
			array('controller' => 'users', 'action' => 'dashboard'),
			array('class' => 'cancel-action')); ?>
<?php echo $this->Form->end(); ?>