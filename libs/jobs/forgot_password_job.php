<?php
class ForgotPasswordJob extends CakeJob {

	var $maintainer;

	var $ipaddress;

	function __construct($maintainer, $ipaddress) {
		$this->maintainer = $maintainer;
		$this->ipaddress = $ipaddress;
	}

	function perform() {
		$this->out('Loading router and setting base url');
		if (!class_exists('Router')) App::import('Core', 'Router');

		if (!defined('FULL_BASE_URL')) {
			define('FULL_BASE_URL', 'http://cakepackages.com');
		}

		$this->out('Retrieving activation key');
		$ipaddress = $this->ipaddress;
		$username = $this->maintainer['username'];
		$this->loadModel('Maintainer');
		$activationKey = $this->Maintainer->changeActivationKey($this->maintainer['id']);

		$this->out('Loading Components');
		$this->loadComponent('Settings.Settings');
		$this->loadComponent('Mail');

		$this->out('Sending mail');
		$this->Mail->send(array(
			'to' => $this->maintainer['email'],
			'mailer' => 'swift',
			'subject' => '[CakePackages] ' . __('Reset Password', true),
			'element' => 'forgot_password',
			'variables' => compact('ipaddress', 'username', 'activationKey'),
		));
	}

}