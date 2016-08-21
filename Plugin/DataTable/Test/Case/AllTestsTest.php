<?php
class AllTestsTest extends PHPUnit_Framework_TestSuite {

	/**
	 * suite
	 *
	 * @return CakeTestSuite
	 */
	public static function suite() {
		$suite = new CakeTestSuite('All Tests');
		$suite->addTestDirectoryRecursive(CakePlugin::path('DataTable') . 'Test' . DS);
		return $suite;
	}
}
