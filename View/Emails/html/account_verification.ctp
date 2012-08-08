<?php echo __('Hello') . ' ' . $userData['User']['username']; ?>,
<br />
<?php echo __('To validate your account, you must visit the URL below within 24 hours') . ': '; ?><br />
<br />
<br />
<?php echo Router::url(array(
		'admin' => false,
		'plugin' => null,
		'controller' => 'users',
		'action' => 'verify',
		$userData['User']['email_token']
	), true);
?>
<br />
<br />
<?php echo __('If you did not request an account, then please ignore this email.'); ?>
<br />
<br />
<?php echo __('IP Address: ') . $ipaddress; ?>