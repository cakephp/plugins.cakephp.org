<?php
class Github extends AppModel {

/**
 * Name of the model.
 *
 * @var string
 * @link http://book.cakephp.org/view/1057/Model-Attributes#name-1068
 */
	public $name = 'Github';

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
 * @param mixed $id Set this ID for this model on startup, can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

		$this->findMethods['blobShow'] = true;
		$this->findMethods['blobShowAll'] = true;
		$this->findMethods['blobShowPath'] = true;

		$this->findMethods['commitsList'] = true;
		$this->findMethods['commitsShowPath'] = true;
		$this->findMethods['commitsShowSha'] = true;

		$this->findMethods['issuesComments'] = true;
		$this->findMethods['issuesLabels'] = true;
		$this->findMethods['issuesList'] = true;
		$this->findMethods['issuesSearch'] = true;
		$this->findMethods['issuesShow'] = true;

		$this->findMethods['reposSearch'] = true;
		$this->findMethods['reposShow'] = true;
		$this->findMethods['reposShowSingle'] = true;
		$this->findMethods['reposShowCollaborators'] = true;
		$this->findMethods['reposShowContributors'] = true;
		$this->findMethods['reposShowLanguages'] = true;
		$this->findMethods['reposShowNetwork'] = true;
		$this->findMethods['reposShowTags'] = true;
		$this->findMethods['reposWatched'] = true;

		$this->findMethods['treeShow'] = true;

		$this->findMethods['userShow'] = true;
		$this->findMethods['userShowFollowing'] = true;
		$this->findMethods['userWatched'] = true;
		$this->findMethods['userEmail'] = true;
	}

	public function _findIssuesSearch($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo', 'term') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$query['state'] = (empty($query['state'])) ? 'open' : $query['state'];
			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
				$query['state'],
				$query['term'],
			));
			unset($query['username'], $query['repo'], $query['state'], $query['term']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findIssuesComments($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo', 'number') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
				$query['number'],
			));
			unset($query['username'], $query['repo'], $query['number']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findIssuesLabels($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
			));
			unset($query['username'], $query['repo']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findIssuesList($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$query['state'] = (empty($query['state'])) ? 'open' : $query['state'];
			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
				$query['state'],
			));
			unset($query['username'], $query['repo'], $query['state']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findIssuesShow($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo', 'number') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
				$query['number'],
			));
			unset($query['username'], $query['repo'], $query['number']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findReposShow($state, $query, $results = array()) {
		if ($state == 'before') {
			return $query;
		} elseif ($state == 'after') {
			if (isset($results['Repository'])) {
				if (empty($results['Repository'])) {
					$results = array();
				} else {
					$results = array($results);
				}
			}
			return $results;
		}
	}

	public function _findReposShowSingle($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
			));
			unset($query['username'], $query['repo']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findReposShowCollaborators($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
			));
			unset($query['username'], $query['repo']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findReposShowContributors($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
			));
			unset($query['username'], $query['repo']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findReposShowNetwork($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
			));
			unset($query['username'], $query['repo']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findReposShowLanguages($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
			));
			unset($query['username'], $query['repo']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findReposShowTags($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
			));
			unset($query['username'], $query['repo']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findCommitsList($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$params['branch'] = (empty($params['branch'])) ? 'master' : $params['branch'];

			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
				$query['branch'],
			));
			unset($query['username'], $query['repo'], $query['branch']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findCommitsShowPath($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo', 'path') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$params['branch'] = (empty($params['branch'])) ? 'master' : $params['branch'];

			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
				$query['branch'],
				$query['path'],
			));
			unset($query['username'], $query['repo'], $query['branch'], $query['path']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findCommitsShowSha($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo', 'sha') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
				$query['sha'],
			));
			unset($query['username'], $query['repo'], $query['sha']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findTreeShow($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo', 'sha') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
				$query['sha'],
			));
			unset($query['username'], $query['repo'], $query['sha']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findBlobShow($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo', 'sha') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
				$query['sha'],
			));
			unset($query['username'], $query['repo'], $query['sha']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findBlobShowAll($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo', 'sha', 'path') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
				$query['sha'],
				$query['path'],
			));
			unset($query['username'], $query['repo'], $query['sha'], $query['path']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _findBlobShowPath($state, $query, $results = array()) {
		if ($state == 'before') {
			foreach (array('username', 'repo', 'sha', 'path') as $param) {
				if (empty($query[$param])) {
					throw new InvalidArgumentException(sprintf("Missing %s param", $param));
				}
			}

			$query['request'] = implode('/', array(
				$query['username'],
				$query['repo'],
				$query['sha'],
				$query['path'],
			));
			unset($query['username'], $query['repo'], $query['sha'], $query['path']);
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	public function _getNewRepositories($username = null) {
		if (!$username) return false;

		$repositories = $this->find('reposShow', $username);
		if (empty($repositories)) {
			return false;
		}

		$Maintainer = ClassRegistry::init('Maintainer');
		$existingUser = $Maintainer->find('view', $username);
		$packages = $Maintainer->Package->find('list', array('conditions' => array(
			'Package.maintainer_id' => $existingUser['Maintainer']['id'])
		));

		$results = array();
		foreach ($repositories as $key => $repository) {
			if (!in_array($repository['Repository']['name'], $packages) && ($repository['Repository']['fork'] != 1)) {
				$results[] = $repository;
			}
		}
		return $results;
	}

	public function _getRelatedRepositories($maintainers = array()) {
		if (!$maintainers) return false;

		$Package = ClassRegistry::init('Package');
		foreach ($maintainers as $i => $maintainer) {
			$repos = $this->find('reposShow', $maintainer['Maintainer']['username']);
			$maintainers[$i]['Repository'] = array();
			if (!empty($repos)) {
				$packages = $Package->find('listformaintainer', $maintainer['Maintainer']['id']);

				foreach ($repos as $j => $repo) {
					if (!in_array($repo['Repository']['name'], $packages) && $repo['Repository']['fork'] != true) {
						$maintainers[$i]['Repository'][] = $repo['Repository'];
					}
				}
			}
		}

		return $maintainers;
	}

	public function _getUnlisted($username = 'josegonzalez') {
		$following = $this->find('usersShowFollowing', 'josegonzalez');
		ClassRegistry::init('Maintainer');
		$maintainer = &new Maintainer;
		$maintainers = $maintainer->find('list', array('fields' => array('username')));
		$maintainers = array_values($maintainers);
		foreach ($following['Users']['User'] as $key => &$user) {
			if (in_array($user, $maintainers)) {
				unset($following['Users']['User'][$key]);
			}
		}
		return $following['Users']['User'];
	}

/**
 * Add a new package for an existing user
 *
 * @param string $username
 * @param string $name
 * @return boolean
 */
	public function savePackage($username, $name) {
		return Resque::enqueue('default', 'NewPackageJob', array('work', $username, $name));
	}

}