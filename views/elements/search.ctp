<?php echo $this->Form->create('SearchIndex', array(
		'class' => 'center',
		'url' => array('plugin' => null, 'controller' => 'packages', 'action' => 'search', 'type' => 'Package'))); ?>
	<?php echo $this->Form->input('SearchIndex.term', array('label' => false)); ?>
	<div id="SearchIndexTerm_autocomplete" ></div>
<?php echo $this->Form->end('Search For Packages'); ?>