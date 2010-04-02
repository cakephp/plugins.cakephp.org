<?php $icons = array(
	'be' => 'behavior',		'cp' => 'component',	'h' => 'helper',
	'm' => 'model',			'c' => 'controller',	'v' => 'view',
	'ds' => 'datasource',	's' => 'shell',			't' => 'theme',
); ?>
<div class="icons">
<?php foreach ($icons as $class => $label): ?>
	<?php if (isset($package) && !$package['contains_'.$label]) $class = 'has ' . $class; ?>
	<?php echo $this->Clearance->link($class, array('controller' => 'packages', 'action' => 'index', $label), array('title' => $label, 'class' => $class))?>
<?php endforeach; ?>
</div>