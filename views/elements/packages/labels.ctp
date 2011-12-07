<ul class="labels">
<?php
	$icons = array(
		'be' => 'behavior',        'h' => 'helper',    'cp' => 'component',
		'm' => 'model',            'v' => 'view',      'c' => 'controller',
		'ds' => 'datasource',      't' => 'theme',     's' => 'shell',
	);
	if (empty($limit)) {
		$limit = false;
	}
	if (empty($full) && $package['Package']['contains_app']) {
		echo $this->Html->tag('li', 'plugin', array('class' => 'plugin'));
		echo '</ul>';
		return;
	}
	$i = 0;
	foreach ($icons as $c => $label) {
		if ($limit && $i >= $limit) {
			break;
		}
		if ($package['Package']['contains_'.$label]) {
			echo $this->Html->tag('li', $label, array('class' => $label));
		}
		$i++;
	}
?>
</ul>