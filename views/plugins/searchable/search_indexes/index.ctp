<?php $this->Html->h2(__('Search Results', true)); ?>
<?php echo $this->element('search'); ?>
<?php if (!empty($results)): ?>
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th><?php __('Contents')?></th>
			<th><?php echo $this->Paginator->sort('name');?></th>
			<th class="actions"><?php __('Actions');?></th>
		</tr>
		<?php $term = (isset($this->data['SearchIndex']['term'])) ? trim($this->data['SearchIndex']['term']) : '';?>
		<?php $i = 0; foreach ($results as $result): ?>
			<tr<?php echo ($i++ % 2 == 0) ? ' class="altrow"' : '';?>>
				<td>
					<?php echo $this->element('search_icons', array('package' => json_decode($result['SearchIndex']['data'], true)))?>
					
				</td>
				<td>
					<?php echo $this->Html->link($result['SearchIndex']['name'],
					 			json_decode($result['SearchIndex']['url'], true)); ?> by
					<?php echo $this->Resource->searchableMaintainer($result['SearchIndex']['data'], array(
						'primary' => 'Maintainer.name', 'fallback' => 'Maintainer.username')); ?><br />
					<?php if (!empty($result['SearchIndex']['summary'])): ?>
						<?php echo $this->Resource->searchableHighlight($result['SearchIndex']['summary'], $term); ?>
					<?php else : ?>
						<?php echo $this->Searchable->snippets($result['SearchIndex']['data']); ?>
					<?php endif; ?>
					&nbsp;
				</td>
				<td class="actions">
					<?php echo $this->Resource->searchableHomepage($result['SearchIndex']['data']); ?>
					<?php echo $this->Resource->searchableEdit($result['SearchIndex']['data']); ?>
					<?php echo $this->Resource->searchableDelete($result['SearchIndex']['data']); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php $params = array_intersect_key($this->params, array_flip(array('type', 'term'))); ?>
	<?php $params = array_map('urlencode', $params); ?>
	<?php $params = array_map('urlencode', $params); ?>
	<?php $this->Paginator->options(array('url' => $params)); ?>
	<p><?php echo $this->Paginator->counter(array(
		'format' => __('Page %page% of %pages%, showing packages %start% to %end%', true)));?></p>
	<div class="paging">
		<?php echo $this->Paginator->prev('<< '.__('previous', true), array(), null,
				array('class' => 'disabled')); ?>
	 | 	<?php echo $this->Paginator->numbers(); ?> |
		<?php echo $this->Paginator->next(__('next', true).' >>', array(), null,
				array('class' => 'disabled')); ?>
	</div>
<?php else: ?>
	<p>Sorry, your search did not return any matches.</p>
<?php endif; ?>