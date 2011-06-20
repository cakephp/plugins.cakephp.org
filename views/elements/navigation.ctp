<?php $types = array(
    'be' => 'behavior',        'h' => 'helper',    'cp' => 'component',
    'm'  => 'model',           'v' => 'view',      'c' => 'controller',
    'ds' => 'datasource',      't' => 'theme',     's' => 'shell',
); ?>
<h4>Filters</h4>
<ul class="icons">
	<?php foreach ($types as $class => $type) : ?>
		<li>
			<?php echo $this->Html->link($class,
					array('plugin' => null, 'controller' => 'packages', 'action' => 'index', 'with' => $type . 's'),
					array('class' => 'icon ' . $class, "title" => "Show packages containing a {$type}")); ?>
			<?php echo $this->Html->link(ucfirst($type) . 's',
					array('plugin' => null, 'controller' => 'packages', 'action' => 'index', 'with' => $type . 's'),
					array('class' => 'text', 'title' => "Show packages containing a {$type}")); ?>
			<br class="clear"/>
		</li>
	<?php endforeach; ?>
</ul>