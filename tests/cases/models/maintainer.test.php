<?php
/* Maintainer Test cases generated on: 2010-02-11 04:02:03 : 1265880123*/
App::import('Model', 'Maintainer');

class MaintainerTestCase extends CakeTestCase {
	var $fixtures = array('app.maintainer', 'app.package');

	function startTest() {
		$this->Maintainer =& ClassRegistry::init('Maintainer');
	}

	function endTest() {
		unset($this->Maintainer);
		ClassRegistry::flush();
	}

}
?>