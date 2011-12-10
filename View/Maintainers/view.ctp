<h2 class="secondary-title">
	<?php echo $this->Html->image('https://secure.gravatar.com/avatar/' . $maintainer['Maintainer']['gravatar_id'], array('alt' => sprintf('Gravatar for %s', $maintainer['Maintainer']['username']), 'class' => 'gravatar')); ?>
	<span class="name"><?php echo ($maintainer['Maintainer']['name']) ? $maintainer['Maintainer']['name'] : $maintainer['Maintainer']['username']; ?></span>
	<div class="github regular">Github Username: <?php echo $this->Html->link($maintainer['Maintainer']['username'], "http://github.com/{$maintainer['Maintainer']['username']}"); ?></div>
	<?php if (!empty($maintainer['Maintainer']['alias'])) : ?><div class="alias regular">Alias: <?php echo $maintainer['Maintainer']['alias']; ?></div><?php endif; ?>
	<?php if (!empty($maintainer['Maintainer']['url'])) : ?>
		<div class="url regular">
			Url: <?php
				if (!strpos($maintainer['Maintainer']['url'], '://')) {$maintainer['Maintainer']['url'] = 'http://' . $maintainer['Maintainer']['url'];}
				echo  $this->Html->link($maintainer['Maintainer']['url'], $maintainer['Maintainer']['url']);
			?>
		</div>
	<?php endif; ?>
	<?php if (!empty($maintainer['Mai regularntainer']['twitter_username'])) : ?>
		<div class="twitter_username">
			Twitter Username: <?php echo $maintainer['Maintainer']['twitter_username']; ?>
		</div>
	<?php endif; ?>
	<br class="clear">
</h2>
<h3 class="secondary-title">
	Packages
</h3>

<?php echo $this->Session->flash(); ?>

<?php if (!empty($maintainer['Package'])):?>
	<?php foreach ($maintainer['Package'] as $package): ?>
		<article class="package">
			<?php echo $this->Html->link($package['name'],
				array('plugin' => null, 'controller' => 'packages', 'action' => 'view', $maintainer['Maintainer']['username'], $package['name']),
				array('class' => 'name')
			); ?>
			<p class="description"><?php echo $this->Resource->description($package['description']); ?></p>
			<div class="meta">
				<!-- <span class="category"></span> -->
				<span class="watchers"><?php echo $package['watchers'] . ' ' . __n('watcher', 'watchers', $package['watchers']); ?></span>
				<span class="maintainer">Maintained by <?php $name = trim($maintainer['Maintainer']['name']); echo $this->Html->link((!empty($name)) ? $name : $maintainer['Maintainer']['username'],
					array('plugin' => null, 'controller' => 'maintainers', 'action' => 'view', $maintainer['Maintainer']['username']),
					array('class' => 'maintainer_name')
				); ?></span>
				<span class="last-pushed">Last Pushed: <?php echo $this->Time->niceShort($package['last_pushed_at']); ?></span>
				<!-- <span class="tags">
					<a href="#">database</a>
					<a href="#">logging</a>
					<a href="#">library</a>
				</span> -->
			</div>
		</article>
	<?php endforeach; ?>
<?php endif; ?>
