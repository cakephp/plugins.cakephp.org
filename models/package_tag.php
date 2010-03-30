<?php
class PackageTag extends AppModel {
	var $name = 'PackageTag';
	var $belongsTo = array(
		'Package',
		'Tag'
	);
}
?>