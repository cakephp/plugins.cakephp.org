<?php
class MaintainersController extends AppController {
	var $name = 'Maintainers';
	var $helpers = array('Maintainer');

	function beforeFilter() {
		parent::beforeFilter();
		if (Configure::read() == 0 && in_array($this->params['action'], array('add', 'edit', 'delete'))) {
			$this->Session->setFlash(__('Access denied', true));
			$this->redirect('/');
		}
	}

	function index() {
		$this->Maintainer->recursive = 0;
		$this->set('maintainers', $this->paginate());
	}

	function view($username = null) {
		if (!$username) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'maintainer'));
			$this->redirect(array('action' => 'index'));
		}
		$maintainer = $this->Maintainer->find('view', $username);
		if (!$maintainer) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'maintainer'));
			$this->redirect(array('action' => 'index'));
		}
		$this->set(compact('maintainer'));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Maintainer->create();
			if ($this->Maintainer->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), 'maintainer'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please, try again.', true), 'maintainer'));
			}
		}
	}

	function edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'maintainer'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Maintainer->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), 'maintainer'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please, try again.', true), 'maintainer'));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Maintainer->read(null, $id);
		}
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid id for %s', true), 'maintainer'));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Maintainer->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), 'Maintainer'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), 'Maintainer'));
		$this->redirect(array('action' => 'index'));
	}
}
?>