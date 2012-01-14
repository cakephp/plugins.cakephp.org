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
				'placeholder' => __('search (ex. debug watchers:5 forks:8 has:component)')
			));
		?>
		<?php
			echo $this->Form->button(__('Search'), array(
				'class' => 'button big icon search',
				'div' => false,
			));
		?>
		<?php echo $this->Form->end();?>
	</div>
</section>


<div class="clearfix columns">
	<section class="packages">
		<?php if (empty($packages)) : ?>
			<?php echo $this->element('no-results'); ?>
		<?php else : ?>
			<?php foreach ($packages as $package) : ?>
				<article>
					<?php echo $this->element('preview', array(
						'package' => $package['Package'],
						'maintainer' => $package['Maintainer'],
						'showLastPushedAt' => false,
					)); ?>
				</article>
			<?php endforeach; ?>
		<?php endif; ?>
	</section>

	<section class="sidebar">
		<?php echo $this->element('search-legend'); ?>
		<?php echo $this->element('suggest'); ?>
	</section>
</div>

<?php
$this->Paginator->options(array(
	'url' => $this->passedArgs
));
?>
<?php if ($this->Paginator->hasNext() || $this->Paginator->hasPrev()): ?>
	<div class="paging">
		<?php echo $this->Paginator->prev('prev', array(), null, array('class' => 'disabled'));?>
		<?php echo $this->Paginator->numbers(array('separator' => ' ', 'modulus' => 8));?>
		<?php echo $this->Paginator->next('next', array(), null, array('class' => 'disabled'));?>
	</div>
<?php endif; ?>