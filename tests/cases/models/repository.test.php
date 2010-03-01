<?php
/* Package Test cases generated on: 2010-02-11 04:02:03 : 1265880123*/
App::import('Model', 'Package');

class PackageTestCase extends CakeTestCase {
	var $fixtures = array('app.package', 'app.maintainer', 'app.package_type', 'app.tag', 'app.packages_tag');

	function startTest() {
		$this->Package =& ClassRegistry::init('Package');
	}

	function endTest() {
		unset($this->Package);
		ClassRegistry::flush();
	}

}
?>