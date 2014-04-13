<?php
/**
 * All Sanction plugin tests
 */
class AllSanctionTest extends CakeTestCase {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All Sanction test');

		$path = CakePlugin::path('Sanction') . 'Test' . DS . 'Case' . DS;
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}

}
