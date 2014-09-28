<?php $this->set('_bodyClass', $_bodyClass . ' page-form'); ?>
<h2>Login</h2>

<?php echo $this->Session->flash(); ?>

<div class="row">
	<div class="col-md-6">
		<p class="description">Use your login from any of the Official CakePHP websites</p>
		<?php
			echo $this->Form->create('User', array(
				'class' => 'UserLoginForm',
				'role' => 'form',
				'url' => array('controller' => 'users', 'action' => 'login'),
			));

			echo $this->Form->input('email', array(
				'class' => 'form-control email',
				'div' => array('class' => 'form-group'),
				'placeholder' => __('enter email address'),
				'type' => 'text',
			));
			echo $this->Form->input('passwd', array(
				'class' => 'form-control password',
				'div' => array('class' => 'form-group'),
				'label' => 'Password',
				'placeholder' => __('enter password'),
			));
			echo $this->Form->input('remember_me', array(
				'after' => __('Remember Me') . '</label>',
				'before' => '<label>',
				'div' => array('class' => 'checkbox'),
				'label' => false,
				'type' => 'checkbox',
			));

			echo $this->Form->hidden('return_to', array('value' => $return_to));

			echo $this->Form->button(__('Login'), array(
				'class' => 'btn btn-primary',
				'div' => false,
			));

			echo $this->Html->link(__('Forgot your password?'), array('action' => 'forgot_password'), array(
				'class' => 'after-button'
			));

			echo '<br />';
			echo $this->Form->end();
		?>
	</div>

	<div class="col-md-6">
		<h3><?php echo __('No Account?'); ?></h3>
		<p>
			<?php echo $this->Html->link(__('Create one!'), array('action' => 'register'), array(
				'class' => 'btn btn-success',
				'role' => 'button'
			)); ?>
		</p>
	</div>
</div>
