<?php echo $this->Form->create('SearchIndex', array(
		'class' => 'search-form center',
		'url' => array('plugin' => null, 'controller' => 'packages', 'action' => 'search', 'type' => 'Package'))); ?>
	<?php echo $this->Form->input('SearchIndex.term', array('label' => false)); ?>
<?php echo $this->Form->end('Search For Packages'); ?>