<?php
class TagsController extends AppController {
	var $name = 'Tags';

	function index() {
		$tags = $this->Tag->find('threaded');
		$this->set(compact('tags'));
	}

	function view($name = null) {
		if (!$name) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'tag'));
			$this->redirect(array('controller' => 'packages', 'action' => 'index'));
		}
		$tag = $this->Tag->find('view', $name);
		$this->set(compact('tag'));
	}

	function add() {
		if (!empty($this->data)) {
			$this->Tag->create();
			if ($this->Tag->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), 'tag'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please, try again.', true), 'tag'));
			}
		}
		$parents = $this->Tag->generatetreelist(null, null, null, '- ');
		$this->set(compact('parents'));
	}

	function edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'tag'));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Tag->save($this->data)) {
				$this->Session->setFlash(sprintf(__('The %s has been saved', true), 'tag'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(sprintf(__('The %s could not be saved. Please, try again.', true), 'tag'));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Tag->find('first', array('conditions' => array('Tag.id' => $id)));
			if (!$this->data) {
				$this->Session->setFlash(sprintf(__('Invalid %s', true), 'tag'));
				$this->redirect(array('action' => 'index'));
			}
		}
		$parents = $this->Tag->generatetreelist(array('Tag.id !=' => $id), null, null, '- ');
		$this->set(compact('parents'));
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid id for %s', true), 'tag'));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Tag->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), 'Tag'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), 'Tag'));
		$this->redirect(array('action' => 'index'));
	}
}
?>