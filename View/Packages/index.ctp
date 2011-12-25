<section class="search">
	<h2><?php echo __('Available CakePHP packages'); ?></h2>
	<div>
		<?php echo $this->Form->create(false, array('action' => 'index'));?>
		<?php
			echo $this->Form->input('query', array(
				'label' => __('Find packages'),
				'div' => false,
				'placeholder' => __('Enter Keyword(s)')
			));
		?>
		<?php echo $this->Form->submit(__('Search'), array('div' => false));?>
		<?php echo $this->Form->end();?>
	</div>
</section>


<div class="clearfix columns">
	<section class="packages">
		<?php foreach ($packages as $package) : ?>
			<article>
				<?php echo $this->element('new/preview', array(
					'package' => $package['Package'],
					'maintainer' => $package['Maintainer'],
				)); ?>
			</article>
		<?php endforeach; ?>
	</section>

	<section class="sidebar">
		<?php echo $this->element('new/suggest'); ?>
	</section>
</div>

<?php echo $this->element('new/paging'); ?>
