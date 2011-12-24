<?php
App::uses('DeferredEmail', 'CakeDjjob.Job');
App::uses('Router', 'Routing');

class BaseEmail extends DeferredEmail {

	public function build() {
		parent::build();

		if (!defined('FULL_BASE_URL')) {
			define('FULL_BASE_URL', Configure::read('Settings.FULL_BASE_URL'));
		}

		$this->updateVars(array(
			'transport' => 'Smtp',
			'host' => 'ssl://smtp.gmail.com',
			'port' => 465,
			'timeout' => '30',
			'username' => Configure::read('Email.username'),
			'password' => Configure::read('Email.password')
		));
	}

}