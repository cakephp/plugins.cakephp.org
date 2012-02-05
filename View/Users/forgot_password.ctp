<?php $this->set('_bodyClass', $_bodyClass . ' page-form'); ?>
<h2>Forgot your password?</h2>

<?php echo $this->Session->flash(); ?>

<div class="clearfix columns">
	<section class="main-content">
		<p class="description">
			Please enter the email you used for registration and you'll get an email with further instructions.
		</p>

		<?php
			echo $this->Form->create('User', array(
				'class' => 'UserEmailForm',
				'url' => array('controller' => 'users', 'action' => 'forgot_password'),
				'inputDefaults' => array('label' => false),
			));

			echo $this->Form->input('User.email', array(
				'class' => 'email',
				'placeholder' => __('email address'),
				'type' => 'text'
			));

			echo $this->Form->button(__('Send Reset Email'), array(
				'class' => 'button solid-green',
				'div' => false,
			));

			echo $this->Html->link(__('Already have your password?'), array('action' => 'login'), array(
				'class' => 'after-button'
			));

			echo '<br />';

			echo $this->Form->end();
		?>
	</section>
</div>