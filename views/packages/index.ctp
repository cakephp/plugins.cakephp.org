<?php
	$tabs = array(
		'ratings' => array('text' => __('Rating', true), 'sort' => 'rating'),
		'watchers' => array('text' => __('Watchers', true), 'sort' => 'watchers', 'direction' => 'desc'),
		'title' => array('text' => __('Title', true), 'sort' => 'name'),
		'maintainer' => array('text' => __('Maintainer', true), 'sort' => 'Maintainer.name'),
		'date' => array('text' => __('Date Created', true), 'sort' => 'created_at'),
		'updated' => array('text' => __('Date Updated', true), 'sort' => 'last_pushed_at'),
	);
	if (empty($this->params['named']['sort'])) {
		$this->params['named']['sort'] = 'rating';
	}	
?>
<div class="packages index">
	<h1><?php echo __('Available CakePHP packages'); ?></h1>
	<div class="search">
		<?php echo $this->Form->create(false, array('action' => 'index'));?>
		<?php
			echo $this->Form->input('query', array(
				'label' => __('Find packages', true),
				'div' => false,
				'placeholder' => __('Enter Keyword(s)', true)
			));
		?>
		<?php echo $this->Form->submit(__('Filter', true), array('div' => false));?>
		<?php echo $this->Form->end();?>
	</div>
	<section class="main-content">
		<ul class="ui-tabs-nav">
		<?php
			foreach ($tabs as $k => $tab) :
					$sortClass = null;
					$direction = null;
				if (!empty($this->params['named']['sort']) && $this->params['named']['sort'] === $tab['sort']) {
					$sortClass = 'class="ui-tabs-selected"';
				}
				if (!empty($tab['direction'])) {
					$direction = $tab['direction'];
				}
		?>
				<li <?php echo $sortClass; ?>>
					<?php echo $this->Paginator->sort($tab['text'], $tab['sort'], array('class' => $k) + compact('direction')); ?>
				</li>
		<?php
			endforeach;
		?>
		</ul>
		<div class="packages-list">
			<?php
				foreach ($packages as $i => $package):
					echo $this->element('packages/preview', array('data' => $package, 'description' => true));
				endforeach;
			?>
		</div>
		<?php echo $this->element('paging'); ?>
	</section>
	<aside class="sidebar">
		
	</aside>
</div>