<?php if (empty($results)): ?>
	<?php $this->Html->for_layout(__('Search Results', true), 'h2'); ?>
	<?php $this->Html->for_layout(__('Search Results | ', true), 'title'); ?>
	<div class="meta_listing information">
		Sorry, your search did not return any matches.
	</div>
<?php else : ?>
	<?php $term = (isset($this->data['SearchIndex']['term'])) ? trim($this->data['SearchIndex']['term']) : '';?>
	<?php $this->Html->for_layout(__("Search Results for {$term}", true), 'h2'); ?>
	<?php $this->Html->for_layout(__("Search Results for {$term} | ", true), 'title'); ?>
	<?php foreach ($results as $result): ?>
		<div class="meta_listing">
			<div class="prefix_2 grid_2 alpha">
				<?php echo $this->element('search_icons', array(
					'package' => json_decode($result['SearchIndex']['data'], true))); ?>
			</div>
			<div class="suffix_2 grid_6 omega information">
				<?php echo $this->Html->link($result['SearchIndex']['name'],
					json_decode($result['SearchIndex']['url'], true)); ?>
				by
				<?php echo $this->Resource->searchableMaintainer($result['SearchIndex']['data'], array(
					'primary' => 'Maintainer.name', 'fallback' => 'Maintainer.username')); ?>
				<br />
				<?php if (!empty($result['SearchIndex']['summary'])): ?>
					<p><?php echo $this->Resource->searchableHighlight($result['SearchIndex']['summary'], $term); ?></p>
				<?php else : ?>
					<p><?php echo $this->Searchable->snippets($result['SearchIndex']['data']); ?></p>
				<?php endif; ?>
			</div>
			<div class="clear"></div>
		</div>
	<?php endforeach; ?>
	<?php $params = array_intersect_key($this->params, array_flip(array('type', 'term'))); ?>
	<?php $params = array_map('urlencode', $params); ?>
	<?php $params = array_map('urlencode', $params); ?>
	<?php $this->Paginator->options(array('url' => $params)); ?>
	<div class="paging">
		<p>
			<?php echo $this->Paginator->counter(array(
				'format' => __('Page %page% of %pages%, showing packages %start% to %end%', true))); ?>
		</p>
		<?php echo $this->Paginator->prev('<< '.__('previous', true), array(), null,
				array('class' => 'disabled')); ?>
		<?php echo $this->Paginator->numbers(); ?>
		<?php echo $this->Paginator->next(__('next', true).' >>', array(), null,
				array('class' => 'disabled')); ?>
	</div>
<?php endif; ?>