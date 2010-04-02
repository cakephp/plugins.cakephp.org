<?php echo $this->Html->h2(__('Login', true)); ?>
<?php echo $this->Session->flash(); ?>
<?php echo $this->Form->create('User'); ?>
	<?php echo $this->Form->input('User.email',
			array('label' => __('Username', true),
				'placeholder' => __('your email address', true),
				'type' => 'text')); ?>
	<?php echo $this->Form->input('User.password',
			array('label' => __('Password', true),
				'placeholder' => __('your password', true))); ?>
	<?php echo $this->Form->input('User.remember',
			array('label' => __('Remember me for 2 weeks', true),
				'type' => 'checkbox')); ?>
	<?php echo $this->Form->submit(__('Login', true),
			array('div' => 'submit forgot')); ?>
	<?php echo $this->Clearance->link(__('Forgot your password?', true),
		array('plugin' => null, 'controller' => 'users', 'action' => 'forgot_password'),
		array('class' => 'forgot-action')); ?>
<?php echo $this->Form->end(); ?>