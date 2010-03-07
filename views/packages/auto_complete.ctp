<ul>
	<?php foreach ($packages as $package): ?>
		<li><?php echo preg_replace("/".$this->data['SearchIndex']['term']."/i", "<strong>$0</strong>", $package['Package']['name']); ?></li>
	<?php endforeach; ?>
</ul>