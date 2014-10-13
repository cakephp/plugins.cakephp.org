<?php
App::uses('PackageData', 'Lib');
App::uses('Github', 'Model');

class PackageDataTest extends CakeTestCase {

	public function startTest($method) {
		parent::startTest($method);
		$this->Github = $this->getMockForModel('Github', array('find'));
		// $this->Github = new Github;
	}

	public function endTest($method) {
		parent::endTest($method);
		unset($this->Github);
	}

	protected function mockPackageData($githubModel, $username, $repository) {
		$PackageData = $this->getMock(
			'PackageData',
			array('out'),
			array($username, $repository, $githubModel)
		);
		$PackageData->expects($this->any())
			->method('out')
			->will($this->returnValue(null));
		return $PackageData;
	}

	protected function getTestData($type, $username, $repository) {
		$dir = sprintf('%sTest%sData%s%s%s%s', APP, DS, DS, $username, DS, $repository);
		$path = sprintf('%s%s%s.json', $dir, DS, $type);
		$contents = file_get_contents($path);
		return json_decode($contents, true);
	}

	public function testRetrieveWithGithub() {
		$username = 'cakephp';
		$repository = 'debug_kit';

		$testData = $this->getTestData('repository', $username, $repository);
		$this->Github->expects($this->at(0))
					 ->method('find')
					 ->will($this->returnValue($testData));
		$testData = $this->getTestData('contributors', $username, $repository);
		$this->Github->expects($this->at(1))
					 ->method('find')
					 ->will($this->returnValue($testData));
		$testData = $this->getTestData('collaborators', $username, $repository);
		$this->Github->expects($this->at(2))
					 ->method('find')
					 ->will($this->returnValue($testData));

		$packageData = $this->mockPackageData(
			$this->Github,
			$username,
			$repository
		);
		$data = $packageData->retrieve();

		$this->assertTrue($data['contributors'] >= 1);
		$this->assertTrue($data['collaborators'] >= 1);
		$this->assertTrue($data['forks'] >= 1);
		$this->assertTrue($data['watchers'] >= 1);
	}

	public function testRetrieveWithGithubAndInexistentPackage() {
		$username = 'cakephp';
		$repository = 'foobar';

		$testData = $this->getTestData('repository', $username, $repository);
		$this->Github->expects($this->at(0))
					 ->method('find')
					 ->will($this->returnValue($testData));

		$packageData = $this->mockPackageData(
			$this->Github,
			$username,
			$repository
		);

		$data = $packageData->retrieve();
		$this->assertEquals(array('deleted' => true), $data);
	}

	public function testCharacterize() {
		$username = 'cakephp';
		$repository = 'debug_kit';

		$testData = $this->getTestData('files', $username, $repository);
		$this->Github->expects($this->at(0))
					 ->method('find')
					 ->will($this->returnValue($testData));

		$packageData = $this->mockPackageData(
			$this->Github,
			$username,
			$repository
		);
		$actual = $packageData->characterize();
		$expected = array(
			'contains_behavior' => true,
			'contains_tests' => true,
			'contains_lib' => true,
			'contains_controller' => true,
			'contains_elements' => true,
			'contains_helper' => true,
			'contains_resource' => true,
			'contains_panel' => true,
			'contains_log' => true,
			'contains_model' => true,
			'contains_locale' => true,
			'contains_composer' => true,
			'contains_shell' => true,
		 );

		$this->assertCount(13, $actual);
		$this->assertEquals($expected, $actual);
	}

}
