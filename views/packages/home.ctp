<?php $this->Html->for_layout('cakephp code for you to download', 'h2'); ?>
<div class="grid_2 alpha navigation">
<?php
$types = array(
	'model', 'view', 'controller',
	'behavior', 'helper', 'component',
	'datasource', 'theme', 'shell',
); ?>
<?php foreach ($types as $type) : ?>
	<div>
		<?php echo $this->Html->link(ucfirst($type), array('controller' => 'packages', 'action' => 'index', $type)); ?>
	</div>
<?php endforeach; ?>
</div>
<div class="grid_5">
<h3><?php __('Latest Packages'); ?></h3>
<?php foreach ($latest as $package) : ?>
	<div class="list latest">
		<?php echo $this->Html->link($package['Package']['name'], array(
				'controller' => 'packages',
				'action' => 'view',
				'maintainer' => $package['Maintainer']['username'],
				'package' => $package['Package']['name']
			), array('class' => 'package_name'));
		?>
		by
		<?php echo $this->Html->link($package['Maintainer']['username'], array(
				'controller' => 'maintainers',
				'action' => 'view',
				$package['Maintainer']['username'],
			), array('class' => 'maintainer_name'));
		?>
	</div>
<?php endforeach; ?>
</div>
<div class="grid_5 omega">
<h3><?php __('Random Packages'); ?></h3>
<?php foreach ($random as $package) : ?>
	<div class="list random">
		<?php echo $this->Html->link($package['Package']['name'], array(
				'controller' => 'packages',
				'action' => 'view',
				'maintainer' => $package['Maintainer']['username'],
				'package' => $package['Package']['name']
			), array('class' => 'package_name'));
		?>
		by
		<?php echo $this->Html->link($package['Maintainer']['username'], array(
				'controller' => 'maintainers',
				'action' => 'view',
				$package['Maintainer']['username'],
			), array('class' => 'maintainer_name'));
		?>
	</div>
<?php endforeach; ?>
</div>