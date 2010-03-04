<?php
class SettingsController extends SettingsAppController{
	public $name = 'Settings';

	public function index() {
		if (!empty($this->data)) {
			if ($this->Setting->saveAll($this->data['Setting'], array('validate' => 'first'))) {
				$this->Session->setFlash(__('The settings has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The settings could not be saved', true));
			}
		}
		$this->data = $this->Setting->find('all');
	}
}
?>