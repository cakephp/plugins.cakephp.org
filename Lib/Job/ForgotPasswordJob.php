<?php
App::uses('BaseEmail', 'Job');

/**
 * Sends a forgotten password email
 *
 * @package default
 * @todo Update this to send emails using the Users plugin
 */
class ForgotPasswordJob extends BaseEmail {

	public function __construct($maintainer, $ipaddress) {
		parent::__construct(null, compact('maintainer', 'ipaddress'));
	}

	public function build() {
		$vars = $this->getVars();
		parent::build();

		$this->loadModel('Maintainer');
		$activationKey = $this->Maintainer->changeActivationKey($vars['maintainer']['id']);

		$this->_email = $vars['maintainer']['email'];
		$this->updateVars(array(
			'subject' => '[CakePackages] ' . __('Reset Password'),
			'template' => 'forgot_password',
			'variables' => array(
				'ipaddress',
				'username' => $vars['maintainer']['username'],
				'activationKey' => $activationKey
			),
		));
	}

}