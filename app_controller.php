<?php
class AppController extends Controller {
	var $components = array(
		'Authsome.Authsome' => array('model' => 'Maintainer'),
		'Permit',
		'Session',
		'Settings.Settings',
		'SwiftMailer'
		'DebugKit.Toolbar',
	);
	var $helpers = array('Ajax', 'Clearance', 'Form', 'Html', 'Resource', 'Session', 'Time');

	function _mailSetup($to, $subject) {
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