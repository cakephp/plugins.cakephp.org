<?php
class PackagesController extends AppController {
	var $name = 'Packages';
	var $paginate = array('contain' => array('Maintainer'), 'limit' => 10);

	function home() {
		$latest = $this->Package->find('latest');
		$random = $this->Package->find('random');
		$this->set(compact('hot', 'latest', 'random'));
	}

	function index($type = null) {
		if ($type) {
			$this->paginate['conditions'] = array("contains_{$type}" => true);
		}

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
		$tags = $this->Package->Tag->generatetreelist(null, null, null, '- ');
		$this->set(compact('maintainers', 'tags'));
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
		$tags = $this->Package->Tag->generatetreelist(null, null, null, '- ');
		$this->set(compact('maintainers', 'tags'));
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

	function auto_complete() {
		$this->set('packages', $this->Package->find('autocomplete', $this->data['SearchIndex']['term']));
		$this->layout = 'ajax';
	}
}
?>