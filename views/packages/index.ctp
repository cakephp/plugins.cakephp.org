<?php if (empty($packages)) : ?>
	<div class="package_listing">
		No packages found.
	</div>
<?php endif; ?>
<?php if ($this->params['action'] != 'search') : ?>
	<?php foreach ($packages as $package) : ?>
		<div class="package_listing">
			<div class="icons-container">
				<?php echo $this->element('icons', array('package' => $package['Package'], 'search' => false, 'meta' => true)); ?>
			</div>
			<div class="information">
				<?php echo $this->Resource->package($package['Package']['name'], $package['Maintainer']['username']); ?>
				by
				<?php echo $this->Resource->maintainer($package['Maintainer']['name'], $package['Maintainer']['username']); ?>
				<br />
				<p><?php echo $this->Resource->description($package['Package']['description']); ?></p>
			</div>
			<div class="clear"></div>
		</div>
	<?php endforeach; ?>
<?php else : ?>
	<?php foreach ($packages as $package): ?>
		<div class="package_listing">
			<div class="icons-container">
				<?php echo $this->element('icons', array(
					'package' => json_decode($package['SearchIndex']['data'], true), 'search' => true, 'meta' => true)); ?>
			</div>
			<div class="information">
				<?php echo $this->Html->link($package['SearchIndex']['name'],
					json_decode($package['SearchIndex']['url'], true)); ?>
				by
				<?php echo $this->Resource->searchableMaintainer($package['SearchIndex']['data'], array(
					'primary' => 'Maintainer.name', 'fallback' => 'Maintainer.username')); ?>
				<br />
				<?php if (!empty($package['SearchIndex']['summary'])): ?>
					<p><?php echo $this->Resource->searchableHighlight($package['SearchIndex']['summary'], $search); ?></p>
				<?php else : ?>
					<p><?php echo $this->Searchable->snippets($package['SearchIndex']['data']); ?></p>
				<?php endif; ?>
			</div>
			<div class="clear"></div>
		</div>
	<?php endforeach; ?>
<?php endif; ?>

<div class="paging">
	<?php $params = array_intersect_key($this->params, array_flip(array(
		'controller', 'action', 'by', 'type', 'term'))); ?>
	<?php $this->Paginator->options(array('url' => $params)); ?>
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