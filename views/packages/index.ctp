<?php if (empty($packages)) : ?>
	<div class="package-list">
		<article>No packages found.</article>
	</div>
	<?php return; ?>
<?php endif; ?>

<?php $params = array_intersect_key($this->params, array_flip(array(
	'controller', 'action', 'with', 'type', 'term'))); ?>
<?php $params += $this->params['named']; ?>
<?php $this->Paginator->options(array('url' => $params)); ?>

<?php echo $this->element('paging', array(
	'cache' => array('key' => md5(serialize($this->params)), 'time' => '+1 day')
)); ?>

<?php echo $this->Session->flash(); ?>

<div class="package-list">
	<?php if ($this->params['action'] != 'search') : ?>
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
					<span class="last_pushed">Last Pushed: <?php echo $this->Time->niceShort($package['Package']['last_pushed_at']); ?></span>
					<!-- <span class="tags">
						<a href="#">database</a>
						<a href="#">logging</a>
						<a href="#">library</a>
					</span> -->
				</div>
			</article>
		<?php endforeach; ?>
	<?php else : ?>
		<?php foreach ($packages as $i => $package) : ?>
			<article class="package<?php echo ($i%2 == 0) ? ' alt' : '' ?>">
				<?php echo $this->Html->link($package['SearchIndex']['name'], json_decode($package['SearchIndex']['url'], true), array('class' => 'package_name')); ?>
				<p class="description">
					<?php if (!empty($package['SearchIndex']['summary'])): ?>
						<?php echo $this->Resource->searchableHighlight($package['SearchIndex']['summary'], $search); ?>
					<?php else : ?>
						<?php echo $this->Searchable->snippets($package['SearchIndex']['data']); ?>
					<?php endif; ?>
				</p>
				<div class="meta">
					<!-- <span class="category"></span> -->
					<span class="watchers"><?php echo $this->Searchable->data('Package.watchers', $package['SearchIndex']['data']) . ' ' . __n('watcher', 'watchers', $this->Searchable->data('Package.watchers', $package['SearchIndex']['data']), true); ?></span>
					<span class="maintainer">Maintained by <?php echo $this->Resource->searchableMaintainer($package['SearchIndex']['data'], array('primary' => 'Maintainer.name', 'fallback' => 'Maintainer.username')); ?></span>
					<span class="last_pushed"><?php echo $this->Time->niceShort($this->Searchable->data('Package.last_pushed_at', $package['SearchIndex']['data'])); ?></span>
					<!-- <span class="tags">
						<a href="#">database</a>
						<a href="#">logging</a>
						<a href="#">library</a>
					</span> -->
				</div>
			</article>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<?php echo $this->element('paging', array(
	'cache' => array('key' => md5(serialize($this->params)), 'time' => '+1 day')
)); ?>
<div class="pagination">
<?php echo $this->Paginator->prev('prev', array(), null,array('class' => 'disabled')); ?>
<?php echo $this->Paginator->numbers(array('separator' => '')); ?>
<?php echo $this->Paginator->next('next', array(), null, array('class' => 'disabled')); ?>
</div>