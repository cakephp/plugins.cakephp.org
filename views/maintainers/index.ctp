<?php if (empty($maintainers)) : ?>
	<div class="maintainer-list">
		<article>No maintainers found.</article>
	</div>
	<?php return; ?>
<?php endif; ?>

<?php $params = array_intersect_key($this->params, array_flip(array(
	'controller', 'action'))); ?>
<?php $params += $this->params['named']; ?>
<?php $this->Paginator->options(array('url' => $params)); ?>

<?php echo $this->element('paging_maintainer', array(
	'cache' => array('key' => md5(serialize($this->params)), 'time' => '+1 day')
)); ?>

<div class="maintainer-list">
	<?php foreach ($maintainers as $i => $maintainer) : ?>
		<article class="maintainer<?php echo ($i%2 == 0) ? ' alt' : '' ?>">
			<span class="name">
				<?php echo $this->Html->link($maintainer['Maintainer']['username'], array('action' => 'view', $maintainer['Maintainer']['username'])); ?>&nbsp;
				<?php echo ($maintainer['Maintainer']['name'] != ' ' and $maintainer['Maintainer']['name'] != '') ? "({$maintainer['Maintainer']['name']})" : ''; ?>
			</span>
			<?php if (!empty($maintainer['Maintainer']['url'])) : ?>
				<p class="description">
					<?php
						if (!strpos($maintainer['Maintainer']['url'], '://')) {
							$maintainer['Maintainer']['url'] = 'http://' . $maintainer['Maintainer']['url'];
						}
						echo  $this->Html->link($maintainer['Maintainer']['url'], $maintainer['Maintainer']['url']);
					?>
				</p>
			<?php endif; ?>
		</article>
	<?php endforeach; ?>
</div>

<?php echo $this->element('paging_maintainer', array(
	'cache' => array('key' => md5(serialize($this->params)), 'time' => '+1 day')
)); ?>
<div class="pagination">
<?php echo $this->Paginator->prev('prev', array(), null,array('class' => 'disabled')); ?>
<?php echo $this->Paginator->numbers(array('separator' => '')); ?>
<?php echo $this->Paginator->next('next', array(), null, array('class' => 'disabled')); ?>
</div>