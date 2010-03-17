<?php
class GithubController extends AppController {
	var $name = 'Github';
	var $helpers = array('Github');
	var $uses = array('Github', 'Maintainer');

	function index() {
		$this->set('maintainers', $this->Maintainer->find('all'));
	}

	function view($username = null) {
		$user = $this->Github->find('user', $username);
		if (!$user) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'user'));
			$this->redirect(array('action' => 'index'));
		}
		$existing = $this->Maintainer->find('existing', $username);
		if (!$existing) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'user'));
			$this->redirect(array('action' => 'index'));
		}
		$packages = $this->Github->find('new_packages', $username);
		$this->set(compact('existing', 'packages', 'user'));
	}

	function add($username = null) {
		$user = $this->Github->find('user', $username);
		if (!$user) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'user'));
			$this->redirect(array('action' => 'new', $username));
		}
		if ($this->Github->saveUser($username)) {
			$this->Session->setFlash(sprintf(__('%s saved!', true), $username));
			$this->redirect(array('action' => 'view', $username));
		} else {
			$this->Session->setFlash(sprintf(__('%s not saved!', true), $username));
			$this->redirect(array('action' => 'github', $username));
		}
	}

	function add_package($username = null, $package = null) {
		if (!$username || !$package) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), 'parameters'));
				$this->redirect(array('action' => 'existing', '1Marc'));
		}
		if ($this->Github->savePackage($username, $package)) {
			$this->Session->setFlash(sprintf(__('Code for %s saved!', true), $package));
			$this->redirect(array('action' => 'view', $username));
		}
		$this->Session->setFlash(sprintf(__('Code for %s not saved!', true), $package));
		$this->redirect(array('action' => 'view', $username));
	}

	function github($username = null) {
		$user = $this->Github->find('user', $username);
		if (!$user) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'user'));
			$this->redirect(array('action' => 'index'));
		}
		$this->set(compact('user'));
	}
}
?>