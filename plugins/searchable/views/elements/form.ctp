<?php echo $form->create('SearchIndex', array(
		'url' => array('plugin' => 'searchable', 'controller' => 'search_indexes', 'action' => 'index'))); ?>
<?php echo $form->input('term', array('label' => 'Search', 'id' => 'SearchSearch')); ?>
<?php echo $form->end(); ?>
