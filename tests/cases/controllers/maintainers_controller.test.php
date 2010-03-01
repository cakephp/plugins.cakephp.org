<?php
/* Maintainers Test cases generated on: 2010-02-11 04:02:06 : 1265880126*/
App::import('Controller', 'Maintainers');

class TestMaintainersController extends MaintainersController {
	var $autoRender = false;

	function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

class MaintainersControllerTestCase extends CakeTestCase {
	var $fixtures = array('app.maintainer', 'app.package', 'app.tag', 'app.packages_tag');

	function startTest() {
		$this->Maintainers =& new TestMaintainersController();
		$this->Maintainers->constructClasses();
	}

	function endTest() {
		unset($this->Maintainers);
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