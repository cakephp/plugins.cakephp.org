<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<body>
		<?php
		$message = explode("\n", $message);

		foreach ($message as $line):
			echo '<p> ' . $line . '</p>';
		endforeach;
		?>
	</body>
</html>