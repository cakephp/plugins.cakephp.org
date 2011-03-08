<?php $icons = array(
	'be' => 'behavior',		'h' => 'helper',	'cp' => 'component',
	'm' => 'model',			'v' => 'view',		'c' => 'controller',
	'ds' => 'datasource',	't' => 'theme',		's' => 'shell',
); ?>
<div class="icons">
<?php if ($search) : ?>
    <?php foreach ($icons as $class => $label): ?>
        <?php if (isset($package) && !$package[sha1('Package.contains_'.$label)]) $class = 'has ' . $class; ?>
        <?php echo $this->Html->link($class, array('plugin' => null, 'controller' => 'packages', 'action' => 'index', $label), array('title' => $label, 'class' => $class))?>
    <?php endforeach; ?>
<?php else : ?>
    <?php foreach ($icons as $class => $label): ?>
        <?php if (isset($package) && !$package['contains_'.$label]) $class = 'has ' . $class; ?>
        <?php echo $this->Html->link($class, array('controller' => 'packages', 'action' => 'index', $label), array('title' => $label, 'class' => $class))?>
    <?php endforeach; ?>
    <?php endif; ?>
</div>