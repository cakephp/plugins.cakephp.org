<?php
class PermitPanel extends DebugPanel {
	var $plugin = 'sanction';
	var $elementName = 'permit_panel';
	var $title = 'Permit';

	function beforeRender(&$controller) {
		$permit_component =& PermitComponent::getInstance();
		$permit =& Permit::getInstance();
		return array(
			'clearances' => $permit->clearances,
			'executed' => $permit_component->executed
		);
	}
}
?>