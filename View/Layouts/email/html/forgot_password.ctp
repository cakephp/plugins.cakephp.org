<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<body>
		<?php echo __('Hello') . ' ' . $maintainer['Maintainer']['username']; ?>,
		<br />
		<?php echo __('Please visit this link to reset your password'); ?>: <br />
		<?php $url = Router::url(array(
				'controller' => 'users',
				'action' => 'reset_password',
				$maintainer['Maintainer']['username'],
				$activationKey,
			), true);
		?>
		<?php echo $this->Html->link($url, $url); ?>
		<br />
		<br />
		<?php echo __('If you did not request a password reset, then please ignore this email.'); ?>
		<br />
		<br />
		<?php echo __('IP Address: ') . $_SERVER['REMOTE_ADDR']; ?>
	</body>
</html>