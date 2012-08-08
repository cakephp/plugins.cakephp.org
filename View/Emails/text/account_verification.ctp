<?php echo __('Hello') . ' ' . $user['User']['username']; ?>,

<?php
	echo __('To validate your account, you must visit the URL below within 24 hours') . ': ';
	echo Router::url(array(
		'admin' => false,
		'plugin' => null,
		'controller' => 'users',
		'action' => 'verify',
		$user['User']['email_token']
	), true);
?>


<?php echo __('If you did not request an account, then please ignore this email.'); ?>


<?php echo __('IP Address: ') . $ipaddress; ?>