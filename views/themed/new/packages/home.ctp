<h2 class="secondary-title">
	Latest Packages
</h2>

<div class="package-list">
	<?php foreach ($packages as $i => $package) : ?>
		<article class="package<?php echo ($i%2 == 0) ? ' alt' : '' ?>">
			<?php echo $this->Html->link($package['Package']['name'],
				array('plugin' => null, 'controller' => 'packages', 'action' => 'view', $package['Maintainer']['username'], $package['Package']['name']),
				array('class' => 'name')
			); ?>
			<p class="description"><?php echo $this->Resource->description($package['Package']['description']); ?></p>
			<div class="meta">
				<!-- <span class="category"></span> -->
				<span class="watchers"><?php echo $package['Package']['watchers'] . ' ' . __n('watcher', 'watchers', $package['Package']['watchers'], true); ?></span>
				<span class="maintainer">Maintained by <?php $name = trim($package['Maintainer']['name']); echo $this->Html->link((!empty($name)) ? $name : $package['Maintainer']['username'],
					array('plugin' => null, 'controller' => 'maintainers', 'action' => 'view', $package['Maintainer']['username']),
					array('class' => 'maintainer_name')
				); ?></span>
				<span class="last_pushed"><?php echo $this->Time->niceShort($package['Package']['last_pushed_at']); ?></span>
				<!-- <span class="tags">
					<a href="#">database</a>
					<a href="#">logging</a>
					<a href="#">library</a>
				</span> -->
			</div>
		</article>
	<?php endforeach; ?>
</div>
<article class="paging">
	<?php echo $this->Html->link('Browse packages', array('plugin' => null, 'controller' => 'packages', 'action' => 'index')); ?>
</article>