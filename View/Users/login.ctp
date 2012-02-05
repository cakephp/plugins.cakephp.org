<?php $this->set('_bodyClass', $_bodyClass . ' page-form'); ?>
<h2>Login</h2>

<?php echo $this->Session->flash(); ?>
<div class="clearfix columns">
	<section class="main-content">
		<p class="description">Use your login from any of the Official CakePHP websites</p>
		<?php
			echo $this->Form->create('User', array(
				'class' => 'UserLoginForm',
				'url' => array('controller' => 'users', 'action' => 'login'),
				'inputDefaults' => array('label' => false),
			));

			echo $this->Form->input('email', array(
				'class' => 'email',
				'placeholder' => __('email address'),
				'type' => 'text',
			));
			echo $this->Form->input('passwd', array(
				'class' => 'password',
				'placeholder' => __('password'),
			));
			echo $this->Form->input('remember_me', array(
				'div' => array('class' => 'input checkbox clearfix'),
				'label' => __('Remember Me'),
				'type' => 'checkbox',
			));

			echo $this->Form->hidden('return_to', array('value' => $return_to));

			echo $this->Form->button(__('Login'), array(
				'class' => 'button solid-green',
				'div' => false,
			));

			echo $this->Html->link(__('Forgot your password?'), array('action' => 'forgot_password'), array(
				'class' => 'after-button'
			));

			echo '<br />';
			echo $this->Form->end();
		?>
	</section>

	<section class="sidebar">
		<div class="infobox">
			<h3><?php echo __('No Account?'); ?></h3>
			<p>
				<?php echo $this->Html->link(__('Create one!'), array('action' => 'register')); ?>
			</p>
		</div>
	</section>
</div>