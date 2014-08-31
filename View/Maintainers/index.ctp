<?php if (empty($maintainers)) : ?>
	<div class="maintainer-list">
		<article>No maintainers found.</article>
	</div>
	<?php return; ?>
<?php endif; ?>

<div class="maintainer-list">
	<?php foreach ($maintainers as $i => $maintainer) : ?>
		<article class="maintainer<?php echo ($i%2 == 0) ? ' alt' : '' ?>">
			<span class="name">
				<?php echo $this->Html->link($maintainer['Maintainer']['username'], array(
					'action' => 'view',
					'id' => $maintainer['Maintainer']['id'],
					'slug' => $maintainer['Maintainer']['username'],
				)); ?>&nbsp;
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

<?php
$this->Paginator->options(array(
	'url' => $this->passedArgs
));
?>
<?php if ($this->Paginator->hasNext() || $this->Paginator->hasPrev()): ?>
	<div class="paging">
		<?php echo $this->Paginator->prev('prev', array(), null, array('class' => 'disabled'));?>
		<?php echo $this->Paginator->numbers(array('separator' => ' ', 'modulus' => 8));?>
		<?php echo $this->Paginator->next('next', array(), null, array('class' => 'disabled'));?>
	</div>
<?php endif; ?>
