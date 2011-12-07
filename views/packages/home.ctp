<?php
	$sortClass = null;
	if (empty($this->params['named']['sort'])) {
		$sortClass = 'class="ui-tabs-selected"';
	}
?>
<div class="packages index">
	<h1><?php echo __('Latest CakePHP code'); ?></h1>

	<div class="search">
		<?php echo $this->Form->create(null, array('action' => 'index'));?>
		<?php
			echo $this->Form->input('search', array(
				'label' => __('Find packages', true),
				'div' => false,
				'placeholder' => __('Enter Keyword(s)', true)
			));
		?>
		<?php echo $this->Form->submit(__('Filter', true), array('div' => false));?>
		<?php echo $this->Form->end();?>
	</div>
	<section class="main-content">
		<div class="packages-list">
			<?php
				foreach ($packages as $i => $package):
					echo $this->element('packages/preview', array('data' => $package, 'description' => true));
				endforeach;
			?>
		</div>
	</section>
	<aside class="sidebar">
		
	</aside>
</div>