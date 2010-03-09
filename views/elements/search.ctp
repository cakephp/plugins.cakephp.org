<?php echo $this->Form->create('SearchIndex', array(
		'url' => array(
			'plugin' => 'searchable',
			'controller' => 'search_indexes',
			'action' => 'index',
			'type' => 'Package'))); ?>
	<?php echo $this->Ajax->autoComplete('SearchIndex.term', 
		array(
			'plugin' => null,
			'controller' => 'packages',
			'action' => 'auto_complete',
			'type' => 'Package'),
		array('label' => 'Search')); ?>
<?php echo $this->Form->end('Search For Packages'); ?>