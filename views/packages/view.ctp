<h2 class="secondary-title">
	<?php echo $this->element('icons', array('cache' => array(
		'key' => sha1(serialize($package['Package'])), 'time' => '+1 day'
	))); ?>
	
	<span class="name"><?php echo $package['Package']['name']; ?></span>
	<br class="clear" />
</h2>

<article class="package">
	<p class="description"><?php echo $this->Resource->description($package['Package']['description']); ?></p>
	<div class="outbound">
		<div>
			<span class="title">Github Url</span>
			<?php echo $this->Resource->repository($package['Maintainer']['username'], $package['Package']['name']); ?>
		</div>
		<div>
			<span class="title">Clone Url</span>
			<input id="clone" onclick="document.getElementById('clone').focus();document.getElementById('clone').select();" value="<?php echo $this->Resource->clone_url($package['Maintainer']['username'], $package['Package']['name']); ?>" />
		</div>
	</dl>
	
	<div class="meta">
		<!-- <span class="category"></span> -->
		<span class="watchers"><?php echo $package['Package']['watchers'] . ' ' . __n('watcher', 'watchers', $package['Package']['watchers'], true); ?></span>
		<span class="maintainer">Maintained by <?php $name = trim($package['Maintainer']['name']); echo $this->Html->link((!empty($name)) ? $name : $package['Maintainer']['username'],
			array('plugin' => null, 'controller' => 'maintainers', 'action' => 'view', $package['Maintainer']['username']),
			array('class' => 'maintainer_name')
		); ?></span>
		<span class="last_pushed">Last Pushed: <?php echo $this->Time->niceShort($package['Package']['last_pushed_at']); ?></span>
		<!-- <span class="tags">
			<a href="#">database</a>
			<a href="#">logging</a>
			<a href="#">library</a>
		</span> -->
	</div>
</article>


<article class="description">
	<?php echo $this->element('rss_reader', array(
		'url' => "https://github.com/{$package['Maintainer']['username']}/{$package['Package']['name']}/commits/master.atom",
		'cache' => array(
			'key' => 'package.rss.' . md5($package['Maintainer']['username'] . $package['Package']['name']),
			'time' => '+6 hours',
		))); ?>
</article>