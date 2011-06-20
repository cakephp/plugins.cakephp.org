<div class="icons">
	<?php
	$icons = array(
		'be' => 'behavior',        'h' => 'helper',    'cp' => 'component',
		'm' => 'model',            'v' => 'view',      'c' => 'controller',
		'ds' => 'datasource',      't' => 'theme',     's' => 'shell',
	);
	$clear = false;
	foreach ($icons as $class => $label) {
		if ($package['Package']['contains_'.$label]) {
			$clear = true;
			echo $this->Html->link($class,
				array('plugin' => null, 'controller' => 'packages', 'action' => 'filter', 'by' => $label . 's'),
				array(
					'class' => 'icon tooltip ' . $class,
					'label' => $label,
					'title' => 'Includes ' . $label,
			));
		}
	}
	if ($clear) echo '<br class="clear" />';
	?>
</div>