<?php
class ConfigurePanel extends DebugPanel {

	public $elementName = 'configure_panel';

	public $title = 'Configure';

/**
 * Prepare output vars before Controller Rendering.
 *
 * @param \Controller|object $controller Controller reference.
 * @return array
 */
	public function beforeRender(Controller $controller) {
		return array(
			'configure' => Configure::read(),
		);
	}
}
