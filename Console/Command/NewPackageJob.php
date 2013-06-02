<?php
App::uses('AppShell', 'Console/Command');

class NewPackageJob extends AppShell {

	public $uses = array('Maintainer', 'Github');

	public function work() {
		$username = $this->args[0];
		$package_name = $this->args[1];
		$this->out(sprintf('Verifying package uniqueness %s/%s', $username, $package_name));

		try {
			$maintainer = $this->Maintainer->find('view', $username);
		} catch (InvalidArgumentException $e) {
			return $this->out($e->getMessage());
		} catch (NotFoundException $e) {
			$this->out("Maintainer not found, creating...");
			$maintainer = $this->createMaintainer($username);
			if (!$maintainer) {
				return $this->out($e->getMessage());
			}
		} catch (Exception $e) {
			return $this->out('Unable to find maintainer: ' . $e->getMessage());
		}

		$existing = $this->Maintainer->Package->find('list', array('conditions' => array(
				'Package.maintainer_id' => $maintainer['Maintainer']['id'],
				'Package.name' => $package_name
		)));
		if ($existing) {
			$this->out("Package exists! Exiting...");
			return false;
		}

		$this->out('Retrieving repository');
		$repo = $this->Github->find('repository', array(
			'owner' => $username,
			'repo' => $package_name
		));

		$this->out(sprintf("Repo Data: %s", json_encode($repo)));
		if (empty($repo['Repository'])) {
			$this->out("No repo data found! Exiting...");
			return false;
		}

		$this->out('Verifying that package is not a fork');
		if ($repo['Repository']['fork']) {
			$this->out("Package is a fork! Exiting...");
			return false;
		}

		$this->out('Detecting homepage');
		$homepage = $this->getHomepage($repo);

		$this->out('Detecting number of issues');
		$issues = $this->getIssues($repo);

		$this->out('Detecting total number of contributors');
		$contributors = $this->getContributors($repo, $username, $package_name);

		$this->out('Detecting number of collaborators');
		$collaborators = $this->getCollaborators($repo, $username, $package_name);

		$this->out('Saving package');
		$this->Maintainer->Package->create();
		$saved = $this->Maintainer->Package->save(array('Package' => array(
			'maintainer_id' => $maintainer['Maintainer']['id'],
			'name' => $package_name,
			'repository_url' => "git://github.com/{$username}/{$package_name}.git",
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

		// $id = $this->Maintainer->Package->getLastInsertID();
		// $package = $this->Maintainer->Package->setupRepository($id);
		// if ($package) {
		// 	$this->Maintainer->Package->characterize($package);
		// }

		$this->out('Package saved');
	}

	public function createMaintainer($username) {
		$user = $this->Github->find('user', array('user' => $username));

		$data = array('Maintainer' => array(
			'username'    => (isset($user['User']['login']))       ? $user['User']['login'] : '',
			'gravatar_id' => (isset($user['User']['gravatar_id'])) ? $user['User']['name'] : '',
			'name'        => (isset($user['User']['name']))        ? $user['User']['name'] : '',
			'company'     => (isset($user['User']['company']))     ? $user['User']['company'] : '',
			'url'         => (isset($user['User']['blog']))        ? $user['User']['blog'] : '',
			'email'       => (isset($user['User']['email']))       ? $user['User']['email'] : '',
			'location'    => (isset($user['User']['location']))    ? $user['User']['location'] : ''
		));

		$this->Maintainer->create();
		$saved = $this->Maintainer->save($data);

		if (!$saved) {
			$this->out("Error Saving Maintainer");
			$this->out(sprintf("User: %s", json_encode($user)));
			$this->out(sprintf("Data: %s", json_encode($data)));
			$this->out(sprintf("Validation Errors: %s", json_encode($this->Maintainer->validationErrors)));
		}

 		return $this->Maintainer->find('view', $username);
	}

	public function getHomepage($repo) {
		$homepage = $repo['Repository']['html_url'];
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

	public function getContributors($repo, $username, $package_name) {
		$contribs = 1;
		$contributors = $this->Github->find('repository', array(
			'owner' => $username,
			'repo' => $package_name,
			'_action' => 'contributors',
		));

		if (!empty($contributors)) {
			$contribs = count($contributors);
		}
		return $contribs;
	}

	public function getCollaborators($repo, $username, $package_name) {
		$collabs = 1;
		$collaborators = $this->Github->find('repository', array(
			'owner' => $username,
			'repo' => $package_name,
			'_action' => 'collaborators',
		));

		if (!empty($collaborators)) {
			$collabs = count($collaborators);
		}
		return $collabs;
	}

}
