<?php
/* Packages Test cases generated on: 2010-02-11 04:02:06 : 1265880126*/
App::import('Controller', 'Packages');

class TestPackagesController extends PackagesController {
	var $autoRender = false;

	function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

class PackagesControllerTestCase extends CakeTestCase {
	var $fixtures = array('app.package', 'app.maintainer', 'app.package_type', 'app.tag', 'app.packages_tag');

	function startTest() {
		$this->Packages =& new TestPackagesController();
		$this->Packages->constructClasses();
	}

	function endTest() {
		unset($this->Packages);
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