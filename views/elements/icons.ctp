<?php $icons = array(
	'be' => 'behavior',		'h' => 'helper',	'cp' => 'component',
	'm' => 'model',			'v' => 'view',		'c' => 'controller',
	'ds' => 'datasource',	't' => 'theme',		's' => 'shell',
); ?>
<div class="icons">
<?php foreach ($icons as $class => $label): ?>
	<?php if (isset($package) && !$package['contains_'.$label]) $class = 'has ' . $class; ?>
	<?php echo $this->Html->link($class, array('controller' => 'packages', 'action' => 'index', $label), array('title' => $label, 'class' => $class))?>
<?php endforeach; ?>
</div>