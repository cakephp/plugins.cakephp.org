<?php
/* PackagesTags Test cases generated on: 2010-02-11 04:02:06 : 1265880126*/
App::import('Controller', 'PackagesTags');

class TestPackagesTagsController extends PackagesTagsController {
	var $autoRender = false;

	function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

class PackagesTagsControllerTestCase extends CakeTestCase {
	var $fixtures = array('app.packages_tag', 'app.package', 'app.maintainer', 'app.package_type', 'app.tag');

	function startTest() {
		$this->PackagesTags =& new TestPackagesTagsController();
		$this->PackagesTags->constructClasses();
	}

	function endTest() {
		unset($this->PackagesTags);
		ClassRegistry::flush();
	}

	function testIndex() {

	}

	function testView() {

	}

	function testAdd() {

	}

	function testEdit() {

	}

	function testDelete() {

	}

}
?>