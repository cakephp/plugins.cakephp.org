<?php $this->Html->h2(__('Maintainers', true));?>
<table cellpadding="0" cellspacing="0">
<tr>
		<th><?php echo $this->Paginator->sort('username');?></th>
		<th><?php echo $this->Paginator->sort('url');?></th>
</tr>
<?php
$i = 0;
foreach ($maintainers as $maintainer):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
<tr<?php echo $class;?>>
	<td>
		<?php echo $this->Html->link($maintainer['Maintainer']['username'], array('action' => 'view', $maintainer['Maintainer']['username'])); ?>&nbsp;
		<?php echo ($maintainer['Maintainer']['name'] != ' ' and $maintainer['Maintainer']['name'] != '') ? "({$maintainer['Maintainer']['name']})" : ''; ?>
	</td>
	<td><?php
	if (!empty($maintainer['Maintainer']['url'])) {
		if (!strpos($maintainer['Maintainer']['url'], '://')) {
			$maintainer['Maintainer']['url'] = 'http://' . $maintainer['Maintainer']['url'];
		}
		echo  $this->Html->link($maintainer['Maintainer']['url'], $maintainer['Maintainer']['url']);
	} else {
		echo '&nbsp;';
	}
	?>
</tr>
<?php endforeach; ?>
</table>
<p>
<?php
echo $this->Paginator->counter(array(
'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?>	</p>

<div class="paging">
	<?php echo $this->Paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $this->Paginator->numbers();?>
|
	<?php echo $this->Paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled'));?>
</div>