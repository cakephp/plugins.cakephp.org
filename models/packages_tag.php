<?php
class PackagesTag extends AppModel {
	var $name = 'PackagesTag';
	var $belongsTo = array(
		'Package',
		'Tag'
	);
}
?>