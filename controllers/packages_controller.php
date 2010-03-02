<?php
class PackagesController extends AppController {
	var $name = 'Packages';
	var $paginate = array('contain' => array('Maintainer'), 'limit' => 10);

	function beforeFilter() {
		parent::beforeFilter();
		if (Configure::read() == 0 && in_array($this->params['action'], array('add', 'edit', 'delete'))) {
			$this->Session->setFlash(__('Access denied', true));
			$this->redirect('/');
		}
	}

	function index() {
		$packages = $this->paginate();
		$this->set(compact('packages'));
	}

	function view() {
		if (!isset($this->params['maintainer']) || !isset($this->params['package'])) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'package'));
			$this->redirect(array('action' => 'index'));
		}
		$package = $this->Package->find('view', array(
			'package' => $this->params['package'],
			'maintainer' => $this->params['maintainer']));
		if (!$package) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'package'));
			$this->redirect(array('action' => 'index'));
		}
		$this->set(compact('package'));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Package->create();
			if ($this->Package->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), 'package'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please, try again.', true), 'package'));
			}
		}
		$maintainers = $this->Package->Maintainer->find('list');
		$this->set(compact('maintainers'));
	}

	function edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'package'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Package->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), 'package'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please, try again.', true), 'package'));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Package->find('edit', $id);
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), 'package'));
				$this->redirect(array('action' => 'index'));
			}
		}
		$maintainers = $this->Package->Maintainer->find('list');
		$this->set(compact('maintainers'));
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid id for %s', true), 'package'));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Package->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), 'Package'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), 'Package'));
		$this->redirect(array('action' => 'index'));
	}
}
?>