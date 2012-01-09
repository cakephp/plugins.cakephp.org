<?php
class NewPackageJob extends CakeJob {

	public $username;

	public $name;

	public function __construct($username, $name) {
		$this->username = $username;
		$this->name = $name;
	}

	public function perform() {
		$this->out(sprintf('Verifying package uniqueness %s/%s', $this->username, $this->name));
		$this->loadModel('Maintainer');
		$this->loadModel('Github');

		try {
			$maintainer = $this->Maintainer->find('view', $this->username);
		} catch (InvalidArgumentException $e) {
			return $this->out($e->getMessage());
		} catch (NotFoundException $e) {
			$maintainer = $this->createMaintainer();
			if (!$maintainer) {
				return $this->out($e->getMessage());
			}
		} catch (Exception $e) {
			return $this->out('Unable to find maintainer: ' . $e->getMessage());
		}

		$existing = $this->Maintainer->Package->find('list', array('conditions' => array(
				'Package.maintainer_id' => $maintainer['Maintainer']['id'],
				'Package.name' => $this->name
		)));
		if ($existing) return false;

		$this->out('Retrieving repository');
		$repo = $this->Github->find('reposShowSingle', array(
			'username' => $this->username,
			'repo' => $this->name
		));

		$this->out('Verifying that package is not a fork');
		if ($repo['Repository']['fork']) return false;

		$this->out('Detecting homepage');
		$homepage = $this->getHomepage($repo);

		$this->out('Detecting number of issues');
		$issues = $this->getIssues($repo);

		$this->out('Detecting total number of contributors');
		$contributors = $this->getContributors($repo);

		$this->out('Detecting number of collaborators');
		$collaborators = $this->getCollaborators($repo);

		$this->out('Saving package');
		$this->Maintainer->Package->create();
		$saved = $this->Maintainer->Package->save(array('Package' => array(
			'maintainer_id' => $maintainer['Maintainer']['id'],
			'name' => $this->name,
			'repository_url' => "git://github.com/{$repo['Repository']['owner']}/{$repo['Repository']['name']}.git",
			'homepage' => $homepage,
			'description' => $repo['Repository']['description'],
			'contributors' => $contributors,
			'collaborators' => $collaborators,
			'forks' => $repo['Repository']['forks'],
			'watchers' => $repo['Repository']['watchers'],
			'open_issues' => $issues,
			'created_at' => substr(str_replace('T', ' ', $repo['Repository']['created_at']), 0, 19),
			'last_pushed_at' => substr(str_replace('T', ' ', $repo['Repository']['pushed_at']), 0, 19),
		)));

		if (!$saved) {
			return $this->out('Package not saved');
		}

		$id = $this->Maintainer->Package->getLastInsertID();
		$package = $this->Maintainer->Package->setupRepository($id);
		if ($package) {
			$this->Maintainer->Package->characterize($package);
		}

		$this->out('Package saved');
	}

	public function createMaintainer() {
		$user = $this->Github->find('userShow', $this->username);

		$this->Maintainer->create();
		$saved = $this->Maintainer->save(array('Maintainer' => array(
			'username' => $user['User']['login'],
			'gravatar_id' => $user['User']['gravatar_id'],
			'name' => (isset($user['User']['name'])) ? $user['User']['name'] : '',
			'company' => (isset($user['User']['company'])) ? $user['User']['company'] : '',
			'url' => (isset($user['User']['blog'])) ? $user['User']['blog'] : '',
			'email' => (isset($user['User']['email'])) ? $user['User']['email'] : '',
			'location' => (isset($user['User']['location'])) ? $user['User']['location'] : ''
		)));

		if (!$saved) {
			$this->log("Error Saving Maintainer", 'queue');
			$this->log(json_encode($this->Maintainer->validationErrors), 'queue');
		}

 		return $this->Maintainer->find('view', $this->username);
	}

	public function getHomepage($repo) {
		$homepage = (string) $repo['Repository']['url'];
		if (!empty($repo['Repository']['homepage'])) {
			$homepage = $repo['Repository']['homepage'];
		}
		return $homepage;
	}

	public function getIssues($repo) {
		$issues = 0;
		if ($repo['Repository']['has_issues']) {
			$issues = $repo['Repository']['open_issues'];
		}
		return $issues;
	}

	public function getContributors($repo) {
		$contribs = 1;
		$contributors = $this->Github->find('reposShowContributors', array(
			'username' => $this->username,
			'repo' => $this->name
		));

		if (!empty($contributors)) {
			$contribs = count($contributors);
		}
		return $contribs;
	}

	public function getCollaborators($repo) {
		$collabs = 1;
		$collaborators = $this->Github->find('reposShowCollaborators', array(
			'username' => $this->username,
			'repo' => $this->name
		));

		if (!empty($collaborators)) {
			$collabs = count($collaborators);
		}
		return $collabs;
	}

}