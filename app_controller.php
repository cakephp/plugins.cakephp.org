<?php
class AppController extends Controller {
	var $components = array(
		'Authsome.Authsome' => array('model' => 'Maintainer'),
		'Mail',
		'Permit',
		'Session',
		'Settings.Settings',
		'DebugKit.Toolbar',
	);
	var $helpers = array('Ajax', 'Clearance', 'Form', 'Html', 'Resource', 'Session', 'Time');
}
?>