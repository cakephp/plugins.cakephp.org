<?php
class GithubController extends AppController {
	var $name = 'Github';
	var $helpers = array('Github');
	var $uses = array('Github', 'Maintainer');

	function beforeFilter() {
		parent::beforeFilter();
		if (Configure::read() == 0) {
			$this->Session->setFlash(__('Access denied', true));
			$this->redirect('/');
		}
	}

	function index() {
		$this->set('maintainers', $this->Maintainer->find('all'));
	}

	function existing($username = null) {
		if (!$username) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'user'));
			$this->redirect(array('action' => 'index'));
		}
		$user = $this->Github->find('user', $username);
		if (!$user) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'user'));
			$this->redirect(array('action' => 'index'));
		}
		$existing = $this->Maintainer->find('existing', $username);
		$packages = $this->Github->find('new_packages', $username);
		$this->set(compact('existing', 'packages', 'user'));
	}

	function add_maintainer($username = null) {
		if (!$username) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'user'));
			$this->redirect(array('action' => 'user', 'josegonzalez'));
		}
		$user = $this->Github->find('user', $username);
		if (!$user) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'user'));
			$this->redirect(array('action' => 'user', 'josegonzalez'));
		}
		if ($this->Github->saveUser($username)) {
			$this->Session->setFlash(sprintf(__('%s saved!', true), $username));
			$this->redirect(array('action' => 'user', 'josegonzalez'));
		} else {
			$this->Session->setFlash(sprintf(__('%s not saved!', true), $username));
			$this->redirect(array('action' => 'user', 'josegonzalez'));
		}
	}

	function add_package($username = null, $package = null) {
		if (!$username || !$package) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), 'parameters'));
				$this->redirect(array('action' => 'existing', '1Marc'));
		}
		if ($this->Github->savePackage($username, $package)) {
			$this->Session->setFlash(sprintf(__('Code for %s saved!', true), $package));
			$this->redirect(array('action' => 'existing', $username));
		}
		$this->Session->setFlash(sprintf(__('Code for %s not saved!', true), $package));
		$this->redirect(array('action' => 'existing', $username));
	}

	function user($username = null) {
		if (!$username) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'user'));
			$this->redirect(array('action' => 'index'));
		}
		$user = $this->Github->find('user', $username);
		if (!$user) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'user'));
			$this->redirect(array('action' => 'index'));
		}
		$this->set(compact('user'));
	}

}
?>