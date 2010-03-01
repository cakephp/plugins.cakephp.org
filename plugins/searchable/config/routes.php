<?php
Router::connect('/search/:type/:term/*', array(
	'plugin' => 'searchable',
	'controller' => 'search_indexes',
	'action' => 'index',
));
?>