<?php
App::uses('DeferredEmail', 'Console/Command');

class UserVerificationEmailJob extends DeferredEmail {

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