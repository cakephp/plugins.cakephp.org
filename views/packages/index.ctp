<?php $this->Html->h2(__('Browse Packages', true));?>
<?php echo $this->element('search'); ?>
<table cellpadding="0" cellspacing="0">
	<tr>
		<th><?php __('Contents')?></th>
		<th><?php echo $this->Paginator->sort('name');?></th>
		<th class="actions"><?php __('Actions');?></th>
	</tr>
	<?php $i = 0; foreach ($packages as $package): ?>
		<tr<?php echo ($i++ % 2 == 0) ? ' class="altrow"' : '';?>>
			<td>
				<?php echo $this->element('icons', array('package' => $package['Package']))?>
			</td>
			<td>
				<?php echo $this->Resource->package($package['Package']['name'], $package['Maintainer']['username']); ?> by
				<?php echo $this->Resource->maintainer($package['Maintainer']['name'], $package['Maintainer']['username']); ?><br />
				<?php echo $this->Resource->description($package['Package']['description']); ?>&nbsp;
			</td>
			<td class="actions">
				<?php echo $this->Clearance->link(__('Homepage', true), $package['Package']['homepage']); ?>
					<br />
					<?php echo $this->Clearance->link(__('Edit', true), array('action' => 'edit', $package['Package']['id'])); ?>
					<?php echo $this->Clearance->link(__('Delete', true), array('action' => 'delete', $package['Package']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $package['Package']['id'])); ?>
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