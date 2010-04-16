<?php
class AppController extends Controller {
	var $components = array(
		'Authsome.Authsome' => array('model' => 'Maintainer'),
		'Mail',
		'Sanction.Permit' => array('path' => 'Maintainer.Maintainer'),
		'Session',
		'Settings.Settings',
		'DebugKit.Toolbar',
	);
	var $helpers = array('Ajax', 'Sanction.Clearance', 'Form', 'Html', 'Resource', 'Session', 'Time');
}
?>