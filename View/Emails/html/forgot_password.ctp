<?php echo __('Hello') . ' ' . $username; ?>,
<br />
<?php echo __('Please visit this link to reset your password'); ?>: <br />
<?php echo Router::url(array(
		'controller' => 'users',
		'action' => 'reset_password',
		$username,
		$activationKey,
	), true);
?>
<br />
<br />
<?php echo __('If you did not request a password reset, then please ignore this email.'); ?>
<br />
<br />
<?php echo __('IP Address: ') . $ipaddress; ?>