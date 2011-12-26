<?php $this->set('pageClass', $pageClass . ' page-form'); ?>
<h2>Account Registration</h2>

<?php echo $this->Session->flash(); ?>
<div class="clearfix columns">
	<article>
		<p class="description">Use your login from any of the Official CakePHP websites</p>
		<p class="description">
			<?php echo $this->Html->link(__('Have an account?'), array('action' => 'login')); ?>
		</p>
		<?php
			echo $this->Form->create('User');

			echo $this->Form->input('username', array(
				'error' => array(
					'unique_username' => __('Please select a username that is not already in use'),
					'username_min' => __('Must be at least 3 characters'),
					'alpha' => __('Username must contain numbers and letters only'),
					'required' => __('Please choose username'),
				),
				'placeholder' => __('desired username'),
			));
			echo $this->Form->input('email', array(
				'error' => array(
					'isValid' => __('Must be a valid email address'),
					'isUnique' => __('An account with that email already exists')
				),
				'label' => __('E-mail (used as login)'),
				'placeholder' => __('email address'),
			));
			echo $this->Form->input('passwd', array(
				'error' => __('Must be at least 5 characters long'),
				'label' => __('Password'),
				'placeholder' => __('password'),
				'type' => 'password',
			));
			echo $this->Form->input('temppassword', array(
				'error' => __('Passwords must match'),
				'label' => __('Password (confirm)'),
				'placeholder' => __('confirm password'),
				'type' => 'password',
			));
			echo $this->Form->input('tos', array(
				'div' => array('class' => 'input checkbox clearfix'),
				'error' => __('You must verify you have read the Terms of Service'),
				'label' => __('I have read and agreed to ') . $this->Html->link(__('Terms of Service'), array('controller' => 'pages', 'action' => 'tos')), 
				'type' => 'checkbox',
			));
			if (!empty($this->Recaptcha)) {
				echo $this->Recaptcha->display();
			}

			echo $this->Form->submit(__('Register'), array(
				'div' => 'submit forgot',
				'after' => $this->Html->link(__('Have an account?'), array('action' => 'login'))
			));
			echo '<br />';
			echo $this->Form->end();
		?>
	</article>

	<div class="info-sidebar">
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

</div>