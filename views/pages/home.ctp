<?php $this->Html->h2('');?>

<h3>Welcome to the Cake Package Repo.</h3>
<p>Find existing CakePHP code quicker, iterate your code faster, and contribute to the community</p>

<?php echo $this->Form->create('SearchIndex', array(
		'url' => array(
			'plugin' => 'searchable',
			'controller' => 'search_indexes',
			'action' => 'index',
			'type' => 'Package'))); ?>
	<?php echo $this->Form->input('term', array('label' => 'Search')); ?>
<?php echo $this->Form->end('Search For Packages'); ?>