<?php
App::uses('Permit', 'Sanction.Controller/Component');

class PermitPanel extends DebugPanel {

	public $plugin = 'sanction';

	public function beforeRender(Controller $controller) {
		if (empty($controller->Permit->user)) {
			$controller->Permit->user = $controller->Toolbar->Session->read($controller->Permit->settings['path']);
		}
		return array(
			'user' => $controller->Permit->user,
			'routes' => $controller->Permit->routes,
			'executed' => $controller->Permit->executed,
		);
	}

}
