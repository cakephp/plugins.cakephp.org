<?php
/* PackagesTags Test cases generated on: 2010-02-11 06:02:55 : 1265886415*/
App::import('Model', 'PackagesTags');

class PackagesTagsTestCase extends CakeTestCase {
	var $fixtures = array('app.packages_tags', 'app.package', 'app.maintainer', 'app.package_type', 'app.tag', 'app.packages_tag');

	function startTest() {
		$this->PackagesTags =& ClassRegistry::init('PackagesTags');
	}

	function endTest() {
		unset($this->PackagesTags);
		ClassRegistry::flush();
	}

}
?>