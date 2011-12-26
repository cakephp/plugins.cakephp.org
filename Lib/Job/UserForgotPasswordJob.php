<?php
App::uses('BaseEmail', 'Job');

/**
 * Sends a forgotten password email
 *
 * @package default
 * @todo Update this to send emails using the Users plugin
 */
class UserForgotPasswordJob extends BaseEmail {

	public function __construct($user, $ipaddress) {
		parent::__construct(null, compact('user', 'ipaddress'));
	}

	public function build() {
		$vars = $this->getVars();
		parent::build();

		$this->loadModel('User');
		$activationKey = $this->User->changeActivationKey($vars['user']);

		$this->_email = $vars['user']['email'];
		$this->updateVars(array(
			'subject' => '[CakePackages] ' . __('Reset Password'),
			'template' => 'forgot_password',
			'variables' => array(
				'ipaddress' => $vars['ipaddress'],
				'username' => $vars['user']['username'],
				'activationKey' => $activationKey
			),
		));
	}

}