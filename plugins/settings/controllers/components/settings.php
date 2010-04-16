<?php
class SettingsComponent extends Object {
	
/**
 * Loads the settings from the cache/database, writes them to cache,
 * and then uses the Configure class to write them to the Session
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 */
	function initialize(&$controller) {
		$_settings = array();
		if (($_settings = Cache::read('settings')) === false) {
			$controller->loadModel('Settings.Setting');
			$_settings = $controller->Setting->find('all');
			Cache::write('settings', $_settings);
		}
		foreach($_settings as $_setting) {
			if ($_setting['Setting']['value'] !== null) {
				Configure::write("Settings." . $_setting['Setting']['key'], $_setting['Setting']['value']);
			}
		}
	}
}
?>