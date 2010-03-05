<?php $this->Html->h2(__('Welcome to the Cake Package Repo', true)); ?>

<p>Find existing CakePHP code quicker, iterate your code faster, and contribute to the community</p>

<?php echo $this->Form->create('SearchIndex', array(
		'url' => array(
			'plugin' => 'searchable',
			'controller' => 'search_indexes',
			'action' => 'index',
			'type' => 'Package'))); ?>
	<?php echo $this->Form->input('term', array('label' => 'Search')); ?>
<?php echo $this->Form->end('Search For Packages'); ?>
<br />
<div class="grid_2 alpha prefix_quarter suffix_quarter package_list">
<h4><?php __('Latest Packages'); ?></h4>
<?php foreach ($latest as $package) : ?>
	<div><?php echo $this->Resource->package($package['Package']['name'], $package['Maintainer']['username']); ?></div>
<?php endforeach; ?>
</div>
<div class="grid_half"><br /></div>
<div class="grid_2 omega prefix_quarter suffix_quarter package_list">
<h4><?php __('Random Packages'); ?></h4>
<?php foreach ($random as $package) : ?>
	<div><?php echo $this->Resource->package($package['Package']['name'], $package['Maintainer']['username']); ?></div>
<?php endforeach; ?>
</div>