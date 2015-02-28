<?php
App::uses('DeferredEmail', 'Console/Command');

class UserForgotPasswordJob extends DeferredEmail
{
    public $uses = array('User');

    public function build()
    {
        $vars = $this->getVars();
        parent::build();

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
