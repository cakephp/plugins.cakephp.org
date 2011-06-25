<h2 class="secondary-title">
	Reset Password
</h2>

<?php echo $this->Session->flash(); ?>

<article>
	<?php echo $this->Form->create('User', array(
		'url' => array(
			'action' => 'reset_password', $this->params['pass']['0'], $this->params['pass']['1'])));?>
		<?php echo $this->Form->input('Maintainer.password',
				array('label' => __('New Password', true),
					'placeholder' => __('your new password', true))); ?>
		<?php echo $this->Form->submit(__('Change Password', true),
				array('div' => 'submit cancel')); ?>
		<span class="alternate-action">
			or <?php echo $this->Clearance->link(__('login', true),
				array('controller' => 'users', 'action' => 'login')); ?>
		</span>
	<?php echo $this->Form->end(); ?>
</article>