<h2 class="secondary-title">
	Forgot Password
</h2>

<?php echo $this->Session->flash(); ?>

<article>
	<?php echo $this->Form->create('User');?>
	<?php echo $this->Form->input('Maintainer.email',
			array('label' => __('Email', true),
				'placeholder' => 'your email address')); ?>
	<?php echo $this->Form->submit(__('Request Password Reset', true),
			array('div' => 'submit cancel')); ?>
	<span class="alternate-action">
		or <?php echo $this->Clearance->link(__('login', true),
				array('controller' => 'users', 'action' => 'login')); ?>
	</span>
	<?php echo $this->Form->end(); ?>
</article>