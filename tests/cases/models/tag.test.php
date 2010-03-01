<?php
/* Tag Test cases generated on: 2010-02-11 04:02:04 : 1265880124*/
App::import('Model', 'Tag');

class TagTestCase extends CakeTestCase {
	var $fixtures = array('app.tag', 'app.package', 'app.maintainer', 'app.package_type', 'app.packages_tag');

	function startTest() {
		$this->Tag =& ClassRegistry::init('Tag');
	}

	function endTest() {
		unset($this->Tag);
		ClassRegistry::flush();
	}

}
?>