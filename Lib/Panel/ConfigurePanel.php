<?php
class ConfigurePanel extends DebugPanel {

	public $elementName = 'configure_panel';
	public $title = 'Configure';

	public function beforeRender(Controller $controller) {
		return array(
			'configure' => Configure::read(),
		);
	}
}