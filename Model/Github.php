<?php
class Github extends AppModel {

/**
 * Custom database table name, or null/false if no table association is desired.
 *
 * @var string
 * @link http://book.cakephp.org/view/1057/Model-Attributes#useTable-1059
 */
	public $useTable = false;

/**
 * The name of the DataSource connection that this Model uses
 *
 * @var string
 * @link http://book.cakephp.org/view/1057/Model-Attributes#useDbConfig-1058
 */
	public $useDbConfig = 'github';

/**
 * Override the constructor to provide custom model finds
 *
 * @param mixed $modelId Set this ID for this model on startup, can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $datasource DataSource connection name.
 */
	public function __construct($modelId = false, $table = null, $datasource = null) {
		parent::__construct($modelId, $table, $datasource);

		$this->findMethods['files'] = true;
		$this->findMethods['repository'] = true;
		$this->findMethods['user'] = true;
	}

/**
 * Retrieves new repositories for a given user
 *
 * @param string $user username
 * @return mixed false if no repositories, array otherwise
 */
	protected function _getNewRepositories($user = null) {
		if (!$user) {
			return false;
		}

		$_action = 'repos';
		$repositories = $this->find('user', compact('user', '_action'));
		if (empty($repositories)) {
			return false;
		}

		$Maintainer = ClassRegistry::init('Maintainer');
		$existingUser = $Maintainer->find('view', $user);
		$packages = $Maintainer->Package->find('list', array('conditions' => array(
			'Package.maintainer_id' => $existingUser['Maintainer']['id'])
		));

		foreach ($packages as $i => $package) {
			$packages[$i] = strtolower($package);
		}

		$results = array();
		foreach ($repositories as $repository) {
			if (!in_array(strtolower($repository['Repository']['name']), $packages) && ($repository['Repository']['fork'] != 1)) {
				$results[] = $repository;
			}
		}
		return $results;
	}

	protected function _getRelatedRepositories($maintainers = array()) {
		if (!$maintainers) {
			return false;
		}

		$Package = ClassRegistry::init('Package');
		foreach ($maintainers as $i => $maintainer) {
			$user = $maintainer['Maintainer']['username'];
			$_action = 'repos';
			$repos = $this->find('user', compact('user', '_action'));
			$maintainers[$i]['Repository'] = array();
			if (!empty($repos)) {
				$packages = $Package->find('listformaintainer', $maintainer['Maintainer']['id']);
				foreach ($repos as $repo) {
					if (!in_array($repo['Repository']['name'], $packages) && $repo['Repository']['fork'] != true) {
						$maintainers[$i]['Repository'][] = $repo['Repository'];
					}
				}
			}
		}

		return $maintainers;
	}

	protected function _getUnlisted($user = 'josegonzalez') {
		$_action = 'following';
		$following = $this->find('users', compact('user', '_action'));
		ClassRegistry::init('Maintainer');
		$maintainer = new Maintainer;
		$maintainers = $maintainer->find('list', array('fields' => array('username')));
		$maintainers = array_values($maintainers);
		foreach ($following['Users']['User'] as $key => &$user) {
			if (in_array($user, $maintainers)) {
				unset($following['Users']['User'][$key]);
			}
		}
		return $following['Users']['User'];
	}

	public function afterFind($results, $primary = false) {
		$files = Hash::get($results, 'File.tree');
		if ($files === null) {
			return $files;
		}

		$results = array();
		foreach ($files as $file) {
			$results[] = array('File' => $file);
		}
		return $results;
	}

/**
 * Add a new package for an existing user
 *
 * @param string $username name of user
 * @param string $name package name
 * @return bool
 */
	public function savePackage($username, $name) {
		return $this->enqueue('NewPackageJob', array($username, $name));
	}

}
