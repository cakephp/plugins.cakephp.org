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

	function flashAndRedirect($message = null, $redirectTo = array()) {
		if (empty($message)) $message = __('Access Error', true);
		$this->Session->setFlash($message);
		$this->redirect(array_merge($this->redirectTo, $redirectTo));
	}

	function redirectUnless($data = null, $message = null) {
		if (empty($data)) {
			if (empty($message)) $message = __('Access Error', true);
			$this->Session->setFlash($message);
			$this->redirect(array_merge($this->redirectTo, $redirectTo));
		}
	}

	function beforeRender() {
		$this->params['useJsonNative'] = true;
	}

}