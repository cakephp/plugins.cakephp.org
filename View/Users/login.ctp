<?php $this->set('pageClass', $pageClass . ' page-form'); ?>
<h2>Login</h2>

<?php echo $this->Session->flash(); ?>
<div class="columns clearfix">
	<article>
		<p class="description">Use your login from any of the Official CakePHP websites</p>
		<?php
			echo $this->Form->create('User');

			echo $this->Form->input('email', array(
				'label' => __('Email address'),
				'placeholder' => __('email address'),
				'type' => 'text',
			));
			echo $this->Form->input('passwd', array(
				'label' => __('Password'),
				'placeholder' => __('password'),
			));
			echo $this->Form->input('remember_me', array(
				'div' => array('class' => 'input checkbox clearfix'),
				'label' => __('Remember Me'),
				'type' => 'checkbox',
			));

			echo $this->Form->hidden('return_to', array('value' => $return_to));

			echo $this->Form->submit(__('Login'), array(
				'div' => 'submit forgot',
				'after' => $this->Html->link(__('Forgot your password?'), array('action' => 'forgot_password'))
			));
			echo '<br />';
			echo $this->Form->end();
		?>
	</article>

	<div class="info-sidebar">
		<h3><?php echo __('No Account?'); ?></h3>
		<p>
			<?php echo $this->Html->link(__('Create one!'), array('action' => 'register')); ?>
		</p>
	</div>
</div>