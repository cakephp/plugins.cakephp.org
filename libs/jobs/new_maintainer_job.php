<?php
class NewMaintainerJob {

	var $username;

	function __construct($username) {
		$this->username = $username;
	}

	function perform() {
		$this->loadModel('Github');
		$user = $this->Github->find('user_show', $this->username);

		$this->loadModel('Maintainer');
		try {
			$existingUser = $this->Maintainer->find('username', $user['User']['login']);
			return false;
		} catch (Exception $e) {}

		return $this->Maintainer->save(array('Maintainer' => array(
				'username' => $user['User']['login'],
				'gravatar_id' => $user['User']['gravatar-id'],
				'name' => (isset($user['User']['name'])) ? $user['User']['name'] : '',
				'company' => (isset($user['User']['company'])) ? $user['User']['company'] : '',
				'url' => (isset($user['User']['blog'])) ? $user['User']['blog'] : '',
				'email' => (isset($user['User']['email'])) ? $user['User']['email'] : '',
				'location' => (isset($user['User']['location'])) ? $user['User']['location'] : ''
		)));
    }

}