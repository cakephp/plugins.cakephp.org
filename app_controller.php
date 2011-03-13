<?php
class AppController extends Controller {
	var $components = array(
		'Authsome.Authsome' => array('model' => 'Maintainer'),
		'CakeDjjob.CakeDjjob',
		'DebugKit.Toolbar',
		'Mail',
		'RequestHandler',
		'Sanction.Permit' => array(
			'check' => 'group',
			'path' => 'Maintainer.Maintainer'
		),
		'Session',
		'Settings.Settings',
		'Webservice.Webservice',
	);
	var $helpers = array(
        'AssetCompress.AssetCompress',
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
			$redirectTo = array();
			$status = null;
			$exit = true;

			if (is_array($message)) {
				if (isset($message['redirectTo'])) $redirectTo = $message['redirectTo'];
				if (isset($message['status'])) $status = $message['status'];

				if (isset($message['exit'])) $exit = $message['exit'];
				if (isset($message['message'])) $message = $message['message'];
			}

			if ($message) {
				$this->Session->setFlash($message, 'flash/error');
			} else if ($message === null) {
				$this->Session->setFlash(__('Access Error', true), 'flash/error');
			}

			if (is_array($redirectTo)) {
				$redirectTo = array_merge($this->redirectTo, $redirectTo);
			}

			$this->redirect($redirectTo, $status, $exit);
		}
	}

	function beforeRender() {
		$this->params['useJsonNative'] = true;
	}

}