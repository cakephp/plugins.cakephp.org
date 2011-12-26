<?php
App::uses('BaseEmail', 'Job');

class UserVerificationEmailJob extends BaseEmail {

	public function __construct($userData) {
		parent::__construct(null, compact('userData'));
	}

	public function build() {
		$vars = $this->getVars();

		parent::build();

		$this->_email = $vars['userData']['User']['email'];
		$this->updateVars(array(
			'subject' => '[CakePackages] ' . __('Account verification'),
			'template' => 'account_verification',
			'variables' => array(
				'userData' => $vars['userData']
			),
		));
	}

}