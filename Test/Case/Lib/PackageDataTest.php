<?php
App::uses('PackageData', 'Lib');
App::uses('Github', 'Model');

class PackageDataTest extends CakeTestCase {

	public function setUp() {
		$this->Github = new Github();

		parent::setUp();
	}

	public function testRetrieveWithGithub() {
		$username = 'cakephp';
		$packageName = 'cakepackages';

		$packageData = new PackageData($username, $packageName, $this->Github);
		$data = $packageData->retrieve();

		$this->assertTrue($data['contributors'] >= 1);
		$this->assertTrue($data['collaborators'] >= 1);
		$this->assertTrue($data['forks'] >= 1);
		$this->assertTrue($data['watchers'] >= 1);
	}

	public function testRetrieveWithGithubAndInexistentPackage() {
		$username = 'cakephp';
		$packageName = 'foobar';

		$packageData = new PackageData($username, $packageName, $this->Github);
		$data = $packageData->retrieve();
		$this->assertFalse($data);
	}

}