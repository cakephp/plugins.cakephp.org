<?php
App::import('Core', 'Xml');
class Github extends AppModel {
	var $name = 'Github';
	var $useTable = false;
	var $socket = null;

	function http_socket() {
		if (!$this->socket) {
			App::import('Core', 'HttpSocket');
			$this->socket = new HttpSocket();
		}
	}

	function cached_xml_get($request, $var = null, $var_two = null, $var_three = null, $var_four = null) {
		$md5_request = md5(serialize(array($request, $var, $addendum, $var_two)));
		$response = array();

		Cache::set(array('duration' => '+2 days'));
		if (($response = Cache::read("Github.{$md5_request}")) === false) {
			$this->http_socket();
			$xml_response = new Xml($this->socket->get($request . $var . $var_two . $var_three . $var_four));
			$response = Set::reverse($xml_response);
			if (!empty($response['Html'])) return false;
			if (!empty($response['Error'])) return $response;
			Cache::set(array('duration' => '+2 days'));
			Cache::write("Github.{$md5_request}", $response);
		}
		return $response;
	}

	function __findUserShow($username = null) {
		if (!$username) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/user/show/", $username);
	}

	function __findUserShowFollowing($username = null) {
		if (!$username) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/user/show/", $username, "/following");
	}

	function __findUserWatched($username = null) {
		if (!$username) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/repos/watched/", $username);
	}

	function __findUserEmail($username = null) {
		if (!$username) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/user/email/", $username);
	}

	function __findIssuesSearch($params = array()) {
		$params['state'] = (empty($params['state'])) ? 'open' : $params['state'];
		if (empty($params['user']) || empty($params['repo']) || empty($params['term'])) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/issues/search/", $params['user'], "/{$params['repo']}", "/{$params['state']}", "/{$params['term']}");
	}

	function __findIssuesList($params = array()) {
		$params['state'] = (empty($params['state'])) ? 'open' : $params['state'];
		if (empty($params['user']) || empty($params['repo'])) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/issues/list/", $params['user'], "/{$params['repo']}", "/{$params['state']}");
	}

	function __findIssuesShow($params = array()) {
		$params['state'] = (empty($params['state'])) ? 'open' : $params['state'];
		if (empty($params['user']) || empty($params['repo']) || empty($params['number'])) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/issues/show/", $params['user'], "/{$params['repo']}", "/{$params['number']}");
	}

	function __findIssuesComments($params = array()) {
		$params['state'] = (empty($params['state'])) ? 'open' : $params['state'];
		if (empty($params['user']) || empty($params['repo']) || empty($params['number'])) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/issues/comments/", $params['user'], "/{$params['repo']}", "/{$params['number']}");
	}

	function __findIssuesLabels($params = array()) {
		$params['state'] = (empty($params['state'])) ? 'open' : $params['state'];
		if (empty($params['user']) || empty($params['repo']) || empty($params['number'])) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/issues/labels/", $params['user'], "/{$params['repo']}");
	}

	function __findReposWatched($username = null) {
		if (!$username) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/repos/watched/", $username);
	}

	function __findReposSearch($query = null) {
		if (!$query) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/repos/search/", str_replace(' ', '+', $query));
	}

	function __findReposShowSingle($params = array()) {
		if (empty($params['username']) || empty($params['repo'])) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/repos/show/", "/{$params['username']}", "/{$params['repo']}");
	}

	function __findReposShowAll($username = null) {
		if (!$username) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/repos/show/", $username);
	}

	function __findReposShowContributors($params = array()) {
		if (empty($params['username']) || empty($params['repo'])) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/repos/show/", "/{$params['username']}", "/{$params['repo']}", '/contributors');
	}

	function __findReposShowNetwork($params = array()) {
		if (empty($params['username']) || empty($params['repo'])) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/repos/show/", "/{$params['username']}", "/{$params['repo']}", '/network');
	}

	function __findReposShowLanguages($params = array()) {
		if (empty($params['username']) || empty($params['repo'])) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/repos/show/", "/{$params['username']}", "/{$params['repo']}", '/languages');
	}

	function __findReposShowTags($params = array()) {
		if (empty($params['username']) || empty($params['repo'])) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/repos/show/", "/{$params['username']}", "/{$params['repo']}", '/tags');
	}

	function __findCommitsList($params = array()) {
		if (empty($params['username']) || empty($params['repo'])) return false;
		$params['branch'] = (empty($params['branch'])) ? 'master' : $params['branch'];

		return $this->cached_xml_get("http://github.com/api/v2/xml/commits/list/", "/{$params['username']}", "/{$params['repo']}", "/{$params['branch']}");
	}

	function __findCommitsShowPath($params = array()) {
		if (empty($params['username']) || empty($params['repo']) || empty($params['path'])) return false;
		$params['branch'] = (empty($params['branch'])) ? 'master' : $params['branch'];

		return $this->cached_xml_get("http://github.com/api/v2/xml/commits/list/", "/{$params['username']}", "/{$params['repo']}", "/{$params['branch']}", "/{$params['path']}");
	}

	function __findCommitsShowSha($params = array()) {
		if (empty($params['username']) || empty($params['repo']) || empty($params['sha'])) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/commits/show/", "/{$params['username']}", "/{$params['repo']}", "/{$params['sha']}");
	}

	function __findTreeShow($params = array()) {
		if (empty($params['username']) || empty($params['repo']) || empty($params['sha'])) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/tree/show/", "/{$params['username']}", "/{$params['repo']}", "/{$params['sha']}");
	}

	function __findBlobShowPath($params = array()) {
		if (empty($params['username']) || empty($params['repo']) || empty($params['sha']) || empty($params['path'])) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/blob/show/", "/{$params['username']}", "/{$params['repo']}", "/{$params['sha']}", "/{$params['path']}");
	}

	function __findBlobShowAll($params = array()) {
		if (empty($params['username']) || empty($params['repo']) || empty($params['sha']) || empty($params['path'])) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/blob/all/", "/{$params['username']}", "/{$params['repo']}", "/{$params['sha']}");
	}

	function __findBlobShow($params = array()) {
		if (empty($params['username']) || empty($params['repo']) || empty($params['sha']) || empty($params['path'])) return false;

		return $this->cached_xml_get("http://github.com/api/v2/xml/blob/show/", "/{$params['username']}", "/{$params['repo']}", "/{$params['sha']}");
	}

	function __findNewPackages($username = null) {
		if (!$username) return false;
		ClassRegistry::init('Maintainer');
		$maintainer = &new Maintainer;
		$existingUser = $maintainer->find('view', $username);
		$repoList = array();

		Cache::set(array('duration' => '+2 days'));
		if (($repoList = Cache::read("Packages.server.list.{$username}")) === false) {
			App::import(array('HttpSocket', 'Xml'));
			$Socket = new HttpSocket();
			$xmlResponse = new Xml($Socket->get("http://github.com/api/v2/xml/repos/show/{$username}"));
			$repoList = Set::reverse($xmlResponse);
			Cache::set(array('duration' => '+7 days'));
			Cache::write("Packages.server.list.{$username}", $repoList);
		}

		$repos = $maintainer->Package->find('list', array(
			'conditions' => array(
				'Package.maintainer_id' => $existingUser['Maintainer']['id'])));
		if (isset($repoList['Repositories']['Repository']['description'])) {
			if (in_array($repoList['Repositories']['Repository']['name'], $repos)) return false;
			if ($repoList['Repositories']['Repository']['fork']['value'] == 'true') return false;
			return array('0' => $repoList['Repositories']['Repository']);
		} else {
		    if (!isset($repoList['Repositories']['Repository'])) return false;
			foreach ($repoList['Repositories']['Repository'] as $key => $package) {
				if (in_array($package['name'], $repos) || ($package['fork']['value'] == 'true')) {
					unset($repoList['Repositories']['Repository'][$key]);
				}
			}
			return $repoList['Repositories']['Repository'];
		}
	}

	function __findPackage($params = array()) {
		$package = array();

		Cache::set(array('duration' => '+2 days'));
		if (($repoList = Cache::read("Packages.server.list.{$params['user']}")) === false) {
			App::import(array('HttpSocket', 'Xml'));
			$Socket = new HttpSocket();
			$xmlResponse = new Xml($Socket->get("http://github.com/api/v2/xml/repos/show/{$params['user']}/{$params['name']}"));
			$package = Set::reverse($xmlResponse);
			Cache::set(array('duration' => '+7 days'));
			Cache::write("Packages.server.list.{$package}", $package);
		}
		return $package;
	}

	function __findPackages($username = null) {
		if (!$username) return false;
		$repoList = array();

		Cache::set(array('duration' => '+2 days'));
		if (($repoList = Cache::read("Packages.server.list.{$username}")) === false) {
			App::import(array('HttpSocket', 'Xml'));
			$Socket = new HttpSocket();
			$xmlResponse = new Xml($Socket->get("http://github.com/api/v2/xml/repos/show/{$username}"));
			$repoList = Set::reverse($xmlResponse);
			Cache::set(array('duration' => '+7 days'));
			Cache::write("Packages.server.list.{$username}", $repoList);
		}

		return $repoList['Repositories']['Repository'];
	}

	function __findUnlisted($username = 'josegonzalez') {
		$following = $this->find('following', 'josegonzalez');
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

	function __findUser($username = null) {
		if (!$username) return false;
		$user = array();

		Cache::set(array('duration' => '+2 days'));
		if (($repoList = Cache::read("Packages.server.list.{$username}")) === false) {
			App::import(array('HttpSocket', 'Xml'));
			$Socket = new HttpSocket();
			$xmlResponse = new Xml($Socket->get("http://github.com/api/v2/xml/user/show/{$username}"));
			$user = Set::reverse($xmlResponse);
			Cache::set(array('duration' => '+7 days'));
			Cache::write("Packages.server.list.{$username}", $user);
		}

		return $user;
	}

	function savePackage($username, $name) {
		ClassRegistry::init('Maintainer');
		$maintainer = &new Maintainer;
		$existingUser = $maintainer->find('view', $username);
		$repo = $maintainer->Package->find('list', array(
			'conditions' => array(
				'Package.maintainer_id' => $existingUser['Maintainer']['id'],
				'Package.name' => $name)));
		if ($repo) return false;

		$repo = $this->find('package', array('user' => $username, 'name' => $name));
		if ($repo['Repository']['fork']['value'] == 'true') return false;
		$data = array(
			'Package' => array(
				'maintainer_id' => $existingUser['Maintainer']['id'],
				'name' => $name,
				'repository_url' => $repo['Repository']['url'],
				'homepage' => $repo['Repository']['url'],
				'description' => $repo['Repository']['description']));
		return $maintainer->Package->save($data);
	}

	function saveUser($username = null) {
		if (!$username) return false;
		$user = array();

		Cache::set(array('duration' => '+2 days'));
		if (($repoList = Cache::read("Packages.server.list.{$username}")) === false) {
			App::import(array('HttpSocket', 'Xml'));
			$Socket = new HttpSocket();
			$xmlResponse = new Xml($Socket->get("http://github.com/api/v2/xml/user/show/{$username}"));
			$user = Set::reverse($xmlResponse);
			Cache::set(array('duration' => '+7 days'));
			Cache::write("Packages.server.list.{$username}", $user);
		}

		ClassRegistry::init('Maintainer');
		$maintainer = &new Maintainer;
		$existingUser = $maintainer->find('by_username', $user['User']['login']);
		if ($existingUser) {
			CakeLog::write('error', 'dang');
			return false;
		}

		$data = array(
			'Maintainer' => array(
				'username' => $user['User']['login'],
				'gravatar_id' => $user['User']['gravatar-id']));

		$data['Maintainer']['name'] = (isset($user['User']['name'])) ? $user['User']['name'] : '';
		$data['Maintainer']['company'] = (isset($user['User']['company'])) ? $user['User']['company'] : '';
		$data['Maintainer']['url'] = (isset($user['User']['blog'])) ? $user['User']['blog'] : '';
		$data['Maintainer']['email'] = (isset($user['User']['email'])) ? $user['User']['email'] : '';
		$data['Maintainer']['location'] = (isset($user['User']['location'])) ? $user['User']['location'] : '';

		return $maintainer->save($data);
	}
}
?>