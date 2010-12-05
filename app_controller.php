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
	var $helpers = array(
		'Ajax',
		'Sanction.Clearance' => array(
			'check' => 'group',
			'path' => 'Maintainer.Maintainer'
		),
		'Form',
		'Html',
		'Resource',
		'Session',
		'Time'
	);
	var $redirectTo = array('action' => 'index');

	function flashAndRedirect($message, $redirectTo = array()) {
		$this->Session->setFlash($message);
		$this->redirect(array_merge($this->redirectTo, $redirectTo));
	}

	function redirectUnless($data = null) {
		if (empty($data)) {
			$this->Session->setFlash($message);
			$this->redirect(array_merge($this->redirectTo, $redirectTo));
		}
	}
}
?>