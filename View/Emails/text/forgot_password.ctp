<?php echo __('Hello') . ' ' . $maintainer['Maintainer']['username']; ?>,

<?php
	echo __('Please visit this link to reset your password') . ': ';
	echo Router::url(array(
		'controller' => 'users',
		'action' => 'reset_password',
		$maintainer['Maintainer']['username'],
		$activationKey,
	), true);
?>


<?php echo __('If you did not request a password reset, then please ignore this email.'); ?>


<?php echo __('IP Address: ') . $_SERVER['REMOTE_ADDR']; ?>