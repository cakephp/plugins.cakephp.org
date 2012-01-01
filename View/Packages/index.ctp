<?php
	if (!empty($this->request->data['query'])) {
		$title = 'Results for <span>' . $this->request->data['query'] . '</span>';
	} else {
		$title = __('Available CakePHP packages');
	}
?>
<section class="search">
	<?php if (!empty($packages)) : ?>
		<h2><?php echo $title; ?></h2>
	<?php endif; ?>
	<div>
		<?php echo $this->Form->create(false, array('action' => 'index'));?>
		<?php
			echo $this->Form->input('query', array(
				'class' => 'query',
				'div' => false,
				'label' => false,
				'placeholder' => __('Find Packages')
			));
		?>
		<?php echo $this->Form->submit(__('Search'), array('div' => false));?>
		<?php echo $this->Form->end();?>
	</div>
</section>


<div class="clearfix columns">
	<section class="packages">
		<?php if (empty($packages)) : ?>
			<?php echo $this->element('new/no-results'); ?>
		<?php else : ?>
			<?php foreach ($packages as $package) : ?>
				<article>
					<?php echo $this->element('new/preview', array(
						'package' => $package['Package'],
						'maintainer' => $package['Maintainer'],
					)); ?>
				</article>
			<?php endforeach; ?>
		<?php endif; ?>
	</section>

	<section class="sidebar">
		<?php echo $this->element('new/search-legend'); ?>
		<?php echo $this->element('new/suggest'); ?>
	</section>
</div>

<?php echo $this->element('new/paging'); ?>
