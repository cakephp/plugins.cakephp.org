<h2>Search results</h2>
<?php echo $form->create('SearchIndex', array(
		'url' => array('plugin' => 'searchable', 'controller' => 'search_indexes', 'action' => 'index'))); ?>
	<?php echo $form->input('term', array('label' => 'Search')); ?>
	<?php echo $form->input('type', array('empty' => 'All',)); ?>
<?php echo $form->end('View Search Results'); ?>
<?php if (!empty($results)): ?>
	<ul>
	<?php foreach ($results as $result) : ?>
		<li>
			<h3><?php echo $html->link ($result['SearchIndex']['name'],
			 			json_decode($result['SearchIndex']['url'], true)); ?></h3>
			<?php if (!empty($result['SearchIndex']['summary'])): ?>
				<p><?php echo $result['SearchIndex']['summary']; ?></p>
			<?php else : ?>
				<?php echo $searchable->snippets($result['SearchIndex']['data']); ?>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
	</ul>
	<?php $params = array_intersect_key($this->params, array_flip(array('type', 'term'))); ?>
	<?php $params = array_map('urlencode', $params); ?>
	<?php $params = array_map('urlencode', $params); ?>
	<?php $paginator->options(array('url' => $params)); ?>
	<div class="paging">
		<?php echo $paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
		| <?php echo $paginator->numbers();?>
		<?php echo $paginator->next(__('next', true).' >>', array(), null, array('class'=>'disabled'));?>
	</div>
<?php else: ?>
	<p>Sorry, your search did not return any matches.</p>
<?php endif; ?>