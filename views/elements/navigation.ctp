<?php $types = array(
    'be' => 'behavior',        'h' => 'helper',    'cp' => 'component',
    'm'  => 'model',           'c' => 'controller',
    'ds' => 'datasource',      's' => 'shell',
); ?>

<h3>Filters</h3>
<?php echo $this->Form->create(false, array(
		'class' => 'search-form',
		'url' => array('plugin' => null, 'controller' => 'packages', 'action' => 'index'))); ?>
	<div>
		<?php echo $this->Form->text('query', array('class' => 'input', 'placeholder' => 'search?')); ?>
		<input type="submit" value="Go" class="button" />
	</div>
	<br class="clear" />
	<div>
		<?php echo $this->Form->text('watchers', array('class' => 'input', 'placeholder' => 'min watchers?')); ?>
		<br class="clear" />
	</div>
	<br class="clear" />
	<div>
		<?php echo $this->Form->text('since', array('class' => 'input', 'placeholder' => 'last updated?')); ?>
		<br class="clear" />
	</div>
	<br class="clear" />
<?php echo $this->Form->end(null); ?>
<br class="clear" />
<ul class="icons">
	<?php if (!empty($merge)) : ?>
		<?php
			$c = null;
			$mergeSelected = $merge;
			if (isset($merge['with'])) {
				$c = substr($merge['with'], 0, -1);
				unset($mergeSelected['with']);
			}
		?>

		<?php foreach ($types as $class => $type) : ?>
			<?php if ($type == $c) : ?>
				<li class="selected">
					<?php echo $this->Html->link($class,
							array_merge($mergeSelected, array('plugin' => null, 'controller' => 'packages', 'action' => 'index')),
							array('class' => 'icon ' . $class, "title" => "Show packages containing a {$type}")); ?>
					<?php echo $this->Html->link(ucfirst($type) . 's',
							array_merge($mergeSelected, array('plugin' => null, 'controller' => 'packages', 'action' => 'index')),
							array('class' => 'text', 'title' => "Show packages containing a {$type}")); ?>
					<br class="clear"/>
				</li>
			<?php else : ?>
				<li>
					<?php echo $this->Html->link($class,
							array_merge($merge, array('plugin' => null, 'controller' => 'packages', 'action' => 'index', 'with' => $type . 's')),
							array('class' => 'icon ' . $class, "title" => "Show packages containing a {$type}")); ?>
					<?php echo $this->Html->link(ucfirst($type) . 's',
							array_merge($merge, array('plugin' => null, 'controller' => 'packages', 'action' => 'index', 'with' => $type . 's')),
							array('class' => 'text', 'title' => "Show packages containing a {$type}")); ?>
					<br class="clear"/>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php else : ?>
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
	<?php endif; ?>
</ul>