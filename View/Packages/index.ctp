<section class="contribute">
	<div class="bubble-top bubble-border">
		<h2 class="header">Use and Share Open Source CakePHP Code</h2>
	</div>
	<div class="bubble-bottom clearfix">
		<article>
			Signup
		</article>
		<article>
			Create a Package
		</article>
		<article>
			Install Plugins
		</article>
		<article>
			Follow Repositories
		</article>
	</div>
</section>

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
		<div class="search-details clearfix">
			<span class="total"><?php
				echo $this->Paginator->counter(array(
					'format' => __('%count% Packages (Showing search results %start%-%end%)'),
				)); ?>
			</span>

			<div class="sort-group">
				<strong>Sort: </strong>
				<?php echo $this->Resource->sort($order); ?>
				<!-- <a class="active" href="#sort-name">Name</a>
				<a href="#sort-installs">Installs</a>
				<a href="#sort-lastmodified">Last Modified</a> -->
			</div>
		</div>
	</div>
</section>

<div class="clearfix columns">
	<section class="packages main-content">

		<?php if (empty($packages)) : ?>
			<?php echo $this->element('no-results'); ?>
		<?php else : ?>
			<?php foreach ($packages as $package) : ?>
				<article>
					<?php echo $this->element('preview', array(
						'package' => $package['Package'],
						'maintainer' => $package['Maintainer'],
						'showDate' => false,
					)); ?>
				</article>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php $this->Paginator->options(array(
			'Package' => array(
				'paramType' => 'querystring',
			),
			'convertKeys' => array_merge(
				Package::$_allowedFilters,
				array('page', 'limit', 'sort', 'direction')
			),
			'url' => $this->request->query,
		)); ?>

		<?php if ($this->Paginator->hasNext()) : ?>
			<?php echo $this->Paginator->next('Load More', array(
				'class' => 'next-page'
			), null, array('class' => 'disabled next-page'));?>
		<?php endif; ?>

	</section>

	<section class="sidebar">
		<?php echo $this->element('search-legend'); ?>
		<?php echo $this->element('suggest'); ?>
	</section>
</div>
