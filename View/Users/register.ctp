<?php $this->set('_bodyClass', $_bodyClass . ' page-form'); ?>
<h2>Account Registration</h2>

<?php echo $this->Session->flash(); ?>
<div class="clearfix columns">
	<section class="main-content">
		<p class="description">Register for an account to any Official CakePHP website</p>
		<?php
			echo $this->Form->create('User', array(
				'class' => 'UserRegisterForm',
				'url' => array('controller' => 'users', 'action' => 'register'),
				'inputDefaults' => array('label' => false),
			));

			echo $this->Form->input('username', array(
				'class' => 'user',
				'error' => array(
					'unique_username' => __('Please select a username that is not already in use'),
					'username_min' => __('Must be at least 3 characters'),
					'alpha' => __('Username must contain numbers and letters only'),
					'required' => __('Please enter a username'),
				),
				'placeholder' => __('desired username'),
			));
			echo $this->Form->input('email', array(
				'class' => 'email',
				'error' => array(
					'isValid' => __('Must be a valid email address'),
					'isUnique' => __('An account with that email already exists')
				),
				'placeholder' => __('email address (used as login)'),
			));
			echo $this->Form->input('passwd', array(
				'class' => 'password',
				'error' => __('Must be at least 5 characters long'),
				'placeholder' => __('password'),
				'type' => 'password',
			));
			echo $this->Form->input('temppassword', array(
				'class' => 'password',
				'error' => __('Passwords must match'),
				'placeholder' => __('confirm password'),
				'type' => 'password',
			));
			echo $this->Form->input('tos', array(
				'div' => array('class' => 'input checkbox clearfix'),
				'error' => __('You must verify you have read the Terms of Service'),
				'label' => __('I have read and agreed to ') . $this->Html->link(__('Terms of Service'), array('controller' => 'pages', 'action' => 'tos')),
				'type' => 'checkbox',
			));

			echo $this->Form->button(__('Register'), array(
				'class' => 'button solid-green',
				'div' => false,
			));

			echo $this->Html->link(__('Have an account?'), array('action' => 'login'), array(
				'class' => 'after-button'
			));

			echo '<br />';
			echo $this->Form->end();
		?>
	</section>

	<section class="sidebar">
		<div class="infobox">
			<h3><?php echo __('Already registered?'); ?></h3>
			<p><?php
				echo sprintf(
					__('If you are already registered in any of the CakePHP community sites, you just need to %s using your email address and password'),
				$this->Html->link('login', array('controller' => 'users', 'action' => 'login')));
			?></p>
			<h3><?php echo __('What happens after registration?'); ?></h3>
			<p><?php echo __('We will send you a confirmation email to activate your account'); ?></p>
			<p><?php echo __('After activating your account you will be part of the most awesome community of php developers!'); ?></p>
		</div>
	</section>

</div>
