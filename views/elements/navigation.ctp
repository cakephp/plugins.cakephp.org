<?php $types = array(
    'be' => 'behavior',        'h' => 'helper',    'cp' => 'component',
    'm' => 'model',            'v' => 'view',      'c' => 'controller',
    'ds' => 'datasource',      't' => 'theme',     's' => 'shell',
); ?>
<nav>
	<?php foreach ($types as $class => $type) : ?>
		<div class="tooltip_w item">
			<span class="icons divisor"><?php
				echo $this->Html->link($class,
					array('plugin' => null, 'controller' => 'packages', 'action' => 'filter', 'by' => $type . 's'),
					array('class' => $class, "title" => "Show packages containing a {$type}")); ?></span>
			<?php echo $this->Html->link(ucfirst($type) . 's',
					array('controller' => 'packages', 'action' => 'filter', 'by' => $type . 's'),
					array('class' => 'text', 'title' => "Show packages containing a {$type}")); ?>
		</div>
	<?php endforeach; ?>
</nav>