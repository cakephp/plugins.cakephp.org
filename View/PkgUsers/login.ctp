<?php
/**
 * Copyright 2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<div class="main-content">
	<h2><?php __d('spactare', 'Login') ?></h2>
	<fieldset>
		<p>
			<?php echo $this->Html->link(__d('spactare', 'No account? Create one!'), array('action' => 'register')); ?>
		</p>
		<?php
			echo $this->Form->create($model, array(
				'action' => 'login'));
			echo $this->Form->input('email', array(
				'label' => __d('spactare', 'Email')));
			echo $this->Form->input('passwd',  array(
				'label' => __d('spactare', 'Password')));
			echo $this->Form->input('remember_me', array(
				'label' => __d('spactare', 'Remember Me'),
				'type' => 'checkbox'));
			echo $this->Form->hidden('return_to', array('value' => $return_to));
		?>
		<p>
			<?php echo $this->Html->link(__d('spactare', 'Forgot your password?'), array('action' => 'reset_password')); ?>
		</p>
		<?php
			echo $this->Form->end(__d('spactare', 'Submit'));
		?>
	</fieldset>
</div>