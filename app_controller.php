<?php
App::import('Core', 'Controller');
class AppController extends Controller {
	var $components = array(
		'Authsome.Authsome' => array('model' => 'Maintainer'),
		'Mail',
		'RequestHandler',
		'Sanction.Permit' => array('path' => 'Maintainer.Maintainer'),
		'Session',
		'Settings.Settings',
		'DebugKit.Toolbar',
	);
	var $helpers = array('Ajax', 'Sanction.Clearance', 'Form', 'Html', 'Resource', 'Session', 'Time');

	function beforeFilter() {
		if (in_array($this->RequestHandler->ext, array('json', 'xml'))) {
			$this->view = 'WebService';
		}
	}
}
?>