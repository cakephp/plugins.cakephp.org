<?php $this->set('_bodyClass', $_bodyClass . ' page-form'); ?>
<h2>Forgot your password?</h2>

<?php echo $this->Session->flash(); ?>

<article>
	<p class="description">
		Please enter the email you used for registration and you\'ll get an email with further instructions.
	</p>

	<?php
		echo $this->Form->create('User');

		echo $this->Form->input('User.email', array(
			'label' => __('Email'),
			'placeholder' => __('email address'),
			'type' => 'text'
		));

		echo $this->Form->submit(__('Submit'), array('div' => 'submit forgot'));
		echo '<br />';

		echo $this->Form->end();
	?>
</article>