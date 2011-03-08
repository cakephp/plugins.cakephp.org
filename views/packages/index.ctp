<div class="package_index">
	<?php $this->Html->for_layout(__('Browse Packages', true), 'h2'); ?>
	<?php $this->Html->for_layout(__('Browse Packages | ', true), 'title'); ?>
	<?php foreach ($packages as $package) : ?>
		<div class="meta_listing">
			<div class="prefix_2 grid_2 alpha">
				<?php echo $this->element('icons', array('package' => $package['Package'], 'search' => false, 'meta' => true)); ?>
			</div>
			<div class="suffix_2 grid_6 omega information">
				<?php echo $this->Resource->package($package['Package']['name'], $package['Maintainer']['username']); ?>
				by
				<?php echo $this->Resource->maintainer($package['Maintainer']['name'], $package['Maintainer']['username']); ?>
				<br />
				<p><?php echo $this->Resource->description($package['Package']['description']); ?></p>
			</div>
			<div class="clear"></div>
		</div>
	<?php endforeach; ?>

	<div class="paging">
		<p>
			<?php echo $this->Paginator->counter(array(
				'format' => __('Page %page% of %pages%, showing packages %start% to %end%', true))); ?>
		</p>
		<?php echo $this->Paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled')); ?>
		<?php echo $this->Paginator->numbers(); ?>
		<?php echo $this->Paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled')); ?>
	</div>
</div>