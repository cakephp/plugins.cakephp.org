<?php
class AppController extends Controller {
	var $components = array(
		'Authsome.Authsome' => array('model' => 'Maintainer'),
		'Mail',
		'RequestHandler',
		'Sanction.Permit' => array(
			'check' => 'group',
			'path' => 'Maintainer.Maintainer'
		),
		'Session',
		'Settings.Settings',
		'DebugKit.Toolbar',
		'Webservice.Webservice',
	);
	var $helpers = array('Ajax', 'Sanction.Clearance', 'Form', 'Html', 'Resource', 'Session', 'Time');
}
?>