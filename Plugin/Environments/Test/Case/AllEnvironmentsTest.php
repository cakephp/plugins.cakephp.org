<?php
/**
 * All Environments plugin tests
 */
class AllEnvironmentsTest extends CakeTestCase {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All Environments test');

		$path = CakePlugin::path('Environments') . 'Test' . DS . 'Case' . DS;
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}

}
