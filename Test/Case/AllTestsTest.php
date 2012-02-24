<?php
/**
 * AllTests class
 *
 * This test group will run all tests.
 *
 */
class AllTests extends CakeTestSuite {
/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All Tests');
		$suite->addTestDirectoryRecursive(TESTS . 'Case');
		return $suite;
	}
}
