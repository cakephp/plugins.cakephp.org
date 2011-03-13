<?php $this->Html->for_layout('cakephp code for you to download', 'title'); ?>
<?php $this->Html->for_layout('cakephp code for you to download', 'h2'); ?>
<?php $types = array(
		'latest' => array(__('Latest Packages', true), true),
		'random' => array(__('Random Packages', true), false),
	);
?>
<?php foreach ($types as $type => $header): ?>
	<div class="list latest">
		<h3>
			<?php if ($header[1]) : ?>
				<?php echo $this->Html->link($header[0], array('controller' => 'packages', 'action' => $type)); ?>
			<?php else : ?>
				<?php echo $header[0]?>
			<?php endif; ?>
		</h3>
		<ul>
		<?php foreach ($$type as $package) : ?>
			<li>
				<?php echo $this->Html->link($package['Package']['name'], array(
						'controller' => 'packages',
						'action' => 'view',
						$package['Maintainer']['username'],
						$package['Package']['name']
					), array('class' => 'package_name'));
				?>
				<span>
				by
				<?php echo $this->Resource->maintainer($package['Maintainer']['name'], $package['Maintainer']['username']);
				?>
				</span>
			</li>
		<?php endforeach; ?>
		</ul>
	</div>
<?php endforeach; ?>
<div class="clear"></div>