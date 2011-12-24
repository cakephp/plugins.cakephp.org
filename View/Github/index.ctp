<h2>
	Existing Maintainers
</h2>

<?php echo $this->Session->flash(); ?>

<article class="maintainer-list">
	<table cellpadding="0" cellspacing="0" class="meta_listing information">
		<tr>
				<th><?php echo __('Username'); ?></th>
		</tr>
		<?php $i = 0; foreach ($maintainers as $maintainer): ?>
		<tr<?php echo ($i++ % 2 == 0) ? ' class="altrow"' : '';?>>
			<td>
				<?php
					$repo_count = count($maintainer['Repository']);
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
</article>
<article class="paging">
	<?php echo $this->Paginator->counter(array(
		'format' => __('Page %page% of %pages%, showing packages %start% to %end%')); ?>
</article>

<div class="pagination">
	<?php echo $this->Paginator->prev('<< '.__('previous'), array(), null, array('class'=>'disabled')); ?>
	<?php echo $this->Paginator->numbers(array('separator' => '')); ?>
	<?php echo $this->Paginator->next(__('next').' >>', array(), null, array('class' => 'disabled')); ?>
</div>