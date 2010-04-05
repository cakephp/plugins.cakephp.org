<?php
class AppController extends Controller {
	var $components = array(
		'Authsome.Authsome' => array('model' => 'Maintainer'),
		'Mail',
		'Sanction.Permit',
		'Session',
		'Settings.Settings',
		'DebugKit.Toolbar',
	);
	var $helpers = array('Ajax', 'Sanction.Clearance', 'Form', 'Html', 'Resource', 'Session', 'Time');
}
?>