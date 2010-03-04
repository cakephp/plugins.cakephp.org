<?php
$message = explode("\n", $message);

foreach ($message as $line):
	echo '<p> ' . $line . '</p>';
endforeach;
?>