<?php
class AppController extends Controller {
	var $components = array(
		'Authsome.Authsome' => array('model' => 'Maintainer'),
		'Permit',
		'DebugKit.Toolbar',
		'Session',
		'SwiftMailer'
	);
	var $helpers = array('Ajax', 'Form', 'Html', 'Resource', 'Session', 'Time');

/**
 * Catches requests for class members that are unset or not visible to
 * the callee. We check to see if they're asking for a known model and,
 * if they are, will load it up for them.
 * @param string $variable
 * @return mixed
 * @access public
 */
	function __get($variable) {
		if (!isset($this->models)) {
			// We don't want to get this array for every request, store it
			$this->models = Configure::listObjects('model');
		}

		// Is it a model we're trying to access?
		if (in_array($variable, $this->models)) {

			// It is! Lets load it up and return the model...
			$this->loadModel($variable);
			return $this->$variable;
		}
	}

	function __mailSetup($to, $subject) {
		$this->SwiftMailer->smtpType = 'tls';
		$this->SwiftMailer->smtpHost = 'smtp.gmail.com';
		$this->SwiftMailer->smtpPort = 465;
		$this->SwiftMailer->smtpUsername = Configure::read('Settings.SmtpUsername');
		$this->SwiftMailer->smtpPassword = Configure::read('Settings.SmtpPassword');
		$this->SwiftMailer->sendAs = 'html';
		$this->SwiftMailer->from = Configure::read('Settings.ServerEmail');
		$this->SwiftMailer->fromName = Configure::read('Settings.SiteTitle');
		$this->SwiftMailer->replyTo = Configure::read('Settings.ServerEmail');
		$this->SwiftMailer->to = $to;
		$this->SwiftMailer->subject = $subject;
	}
}
?>