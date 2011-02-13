<?php
class NewPackageJob extends CakeJob {

	var $username;

	var $name;

	function __construct($username, $name) {
		$this->username = $username;
		$this->name = $name;
	}

	function perform() {
		$this->out('Verifying package uniqueness');
		$this->loadModel('Maintainer');
		$maintainer = $this->Maintainer->find('view', $this->username);

		$existing = $this->Maintainer->Package->find('list', array('conditions' => array(
				'Package.maintainer_id' => $maintainer['Maintainer']['id'],
				'Package.name' => $this->name
		)));
		if ($existing) return false;

		$this->loadModel('Github');
		$this->out('Retrieving repository');
		$repo = $this->getRepo();

		$this->out('Verifying that package is not a fork');
		if ($this->isFork($repo)) return false;

		$this->out('Detecting homepage');
		$homepage = $this->getHomepage($repo);

		$this->out('Detecting number of issues');
		$issues = $this->getIssues($repo);

		$this->out('Detecting total number of contributors');
		$contributors = $this->getContributors($repo);

		$this->out('Detecting number of collaborators');
		$collaborators = $this->getCollaborators($repo);

		$this->out('Saving package');
		$this->Maintainer->Package->save(array('Package' => array(
			'maintainer_id' => $maintainer['Maintainer']['id'],
			'name' => $this->name,
			'repository_url' => "git://github.com/{$repo['Repository']['owner']}/{$repo['Repository']['name']}.git",
			'homepage' => $homepage,
			'description' => $repo['Repository']['description'],
			'contributors' => $contributors,
			'collaborators' => $collaborators,
			'forks' => $repo['Repository']['forks']['value'],
			'watchers' => $repo['Repository']['watchers']['value'],
			'open_issues' => $issues,
			'created_at' => substr(str_replace('T', ' ', $repo['Repository']['created-at']['value']), 0, 19),
			'last_pushed_at' => substr(str_replace('T', ' ', $repo['Repository']['pushed-at']['value']), 0, 19),
		)));
	}

	function getRepo() {
 		return $this->Github->find('repos_show_single', array(
			'username' => $this->username,
			'repo' => $this->name
		));
	}

	function isFork($repo) {
		return ($repo['Repository']['fork']['value'] == 'true');
	}

	function getHomepage($repo) {
		$homepage = (string) $repo['Repository']['url'];
		if (!empty($repo['Repository']['homepage']['value'])) {
			if (is_array($repo['Repository']['homepage'])) {
 				$homepage = $repo['Repository']['homepage']['value'];
			} else {
 				$homepage = $repo['Repository']['homepage'];
			}
		} else if (!empty($repo['Repsitory']['homepage'])) {
 			$homepage = $repo['Repository']['homepage'];
		}
		return $homepage;
	}

	function getIssues($repo) {
		$issues = 0;
		if ($repo['Repository']['has-issues']['value'] == 'true') {
			$issues = $repo['Repository']['open-issues']['value'];
		}
		return $issues;
	}

	function getContributors($repo) {
		$contribs = 1;
		$contributors = $this->Github->find('repos_show_contributors', array(
			'username' => $this->username,
			'repo' => $this->name
		));
		if (!empty($contributors)) {
			if (!empty($contributors['Contributors']['Contributor'][0])) {
				$contribs = count($contributors['Contributors']['Contributor']);
			}
		}
		return $contribs;
	}

	function getCollaborators($repo) {
		$collabs = 1;
		$collaborators = $this->Github->find('repos_show_collaborators', array(
			'username' => $this->username,
			'repo' => $this->name
		));
		if (!empty($collaborators)) {
			if (!empty($collaborators['Collaborators']['Collaborator']) && is_array($collaborators['Collaborators']['Collaborator'])) {
				$collabs = count($collaborators['Collaborators']['Collaborator']);
			}
		}
		return $collabs;
	}

}