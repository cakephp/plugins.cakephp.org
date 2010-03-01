<?php
class TagsController extends AppController {
	var $name = 'Tags';

	function index($name = null) {
		$tag = $this->Resource->find('index', $name);
		if (!$tag) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'tag'));
			$this->redirect(array('controller' => 'packages', 'action' => 'index'));
		}
		$this->set(compact('tag'));
	}
}
?>