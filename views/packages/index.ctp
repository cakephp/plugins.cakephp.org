<?php $this->Html->h2(__('Packages', true));?>
<?php echo $this->Form->create('SearchIndex', array(
		'url' => array(
			'plugin' => 'searchable',
			'controller' => 'search_indexes',
			'action' => 'index',
			'type' => 'Package'))); ?>
	<?php echo $this->Form->input('term', array('label' => 'Search')); ?>
<?php echo $this->Form->end('Search Packages'); ?>
<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('name');?></th>
			<th class="actions"><?php __('Homepage');?></th>
	</tr>
	<?php $i = 0; foreach ($packages as $package): ?>
		<tr<?php echo ($i++ % 2 == 0) ? ' class="altrow"' : '';?>>
			<td>
				<?php echo $this->Resource->package($package['Package']['name'], $package['Maintainer']['username']); ?> by
				<?php echo $this->Resource->maintainer($package['Maintainer']['name'], $package['Maintainer']['username']); ?><br />
				<?php echo $this->Resource->description($package['Package']['description']); ?>&nbsp;
			</td>
			<td class="actions">
				<?php echo $this->Html->link(__('Homepage', true), $package['Package']['homepage']); ?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
<p>
<?php
echo $this->Paginator->counter(array(
'format' => __('Page %page% of %pages%, showing packages %start% to %end%', true)
));
?></p>

<div class="paging">
	<?php echo $this->Paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled')); ?>
 | 	<?php echo $this->Paginator->numbers(); ?> |
	<?php echo $this->Paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled')); ?>
</div>