<?php $this->set('bodyClass', $bodyClass . ' page-form'); ?>
<h2>Reset your password</h2>

<?php echo $this->Session->flash(); ?>

<article>
	<p class="description">Use this form to reset your account password.</p>

	<?php
		echo $this->Form->create('User', array('url' => array(
			'action' => 'reset_password',
			$token
		)));

		echo $this->Form->input('User.new_password', array(
			'label' => __('New Password'),
			'placeholder' => __('new password'),
			'type' => 'text'
		));
		echo $this->Form->input('User.confirm_password', array(
			'label' => __('Confirm'),
			'placeholder' => __('confirm password')
		));

		echo $this->Form->submit(__('Reset Password'), array('div' => 'submit forgot'));
		echo '<br />';

		echo $this->Form->end();
	?>
</article>