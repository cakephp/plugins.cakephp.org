<?php
class MaintainersController extends AppController {
	var $name = 'Maintainers';
	var $helpers = array('Maintainer');

	function index() {
		$this->paginate = array('index');

		$maintainers = $this->paginate();
		$this->set(compact('maintainers'));
	}

	function view($username = null) {
		try {
			$this->set('maintainer', $maintainer = $this->Maintainer->find('view', $username));
		} catch (Exception $e) {
			$this->flashAndRedirect($e->getMessage());
		}
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
			$this->flashAndRedirect(__('Invalid maintainer', true));
		}
		if (!empty($this->data)) {
			if ($this->Maintainer->save($this->data)) {
				$this->flashAndRedirect(__('The maintainer has been saved', true));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please, try again.', true), 'maintainer'));
			}
		}
		if (empty($this->data)) {
			try {
				$this->data = $this->Maintainer->find('edit', $id);
			} catch (Exception $e) {
				$this->flashAndRedirect($e->getMessage());
			}
			$this->redirectUnless($this->data);
		}
	}

	function delete($id = null) {
		$this->redirectUnless($id);

		if ($this->Maintainer->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), 'Maintainer'));
		} else {
			$this->Session->setFlash(sprintf(__('%s was not deleted', true), 'Maintainer'));
		}

		$this->redirect(array('action' => 'index'));
	}

}