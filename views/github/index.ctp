<?php $this->Html->h2(__('Existing Maintainers', true));?>
<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo __('Username', true); ?></th>
	</tr>
	<?php $i = 0; foreach ($maintainers as $maintainer): ?>
	<tr<?php echo ($i++ % 2 == 0) ? ' class="altrow"' : '';?>>
		<td>
			<?php
				$repo_count = count($maintainer['Repositories']);
				if ($repo_count > 0) {
					if (strlen($repo_count) == 1) $repo_count = "0{$repo_count}";
					echo "<span class='count'>{$repo_count}</span>";
				}
			?>
			<?php echo $this->Github->existing($maintainer['Maintainer']['username'], $maintainer['Maintainer']['name']); ?>
		</td>
	</tr>
	<?php endforeach; ?>
</table>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(sprintf(__('List %s', true), __('Packages', true)), array('controller' => 'packages', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Clearance->link(sprintf(__('New %s', true), __('Package', true)), array('controller' => 'packages', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="paging">
	<?php echo $this->Paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $this->Paginator->numbers();?>
|
	<?php echo $this->Paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled'));?>
</div>