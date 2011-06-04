<?php
class UsersController extends AppController {

/**
 * The name of this controller. Controller names are plural, named after the model they manipulate.
 *
 * @var string
 * @access public
 * @link http://book.cakephp.org/view/959/Controller-Attributes
 */
    var $name = 'Users';

/**
 * An array containing the class names of models this controller uses.
 *
 * Example: `var $uses = array('Product', 'Post', 'Comment');`
 *
 * Can be set to array() to use no models.  Can be set to false to
 * use no models and prevent the merging of $uses with AppController
 *
 * @var mixed A single name as a string or a list of names as an array.
 * @access protected
 * @link http://book.cakephp.org/view/961/components-helpers-and-uses
 */
    var $uses = array('Maintainer');

/**
 * Dashboard action for the user panel
 *
 * @return void
 */
    function dashboard() {
        $user = $this->Maintainer->find('dashboard');
        $this->set(compact('user'));
    }

/**
 * Login action. Supports logging in via email or username, as well as
 * implementing remember_me functionality
 *
 * @return void
 */
    function login() {
        if (empty($this->data)) {
            return;
        }

        $type = (strstr($this->data['User']['login'], '@')) ? 'credentials' : 'username';

        $maintainer = Authsome::login($type, $this->data['User']);

        if (!$maintainer) {
            $this->Session->setFlash(__('Unknown user or incorrect Password', true));
            return;
        }

        $remember = (!empty($this->data['Maintainer']['remember']));
        if ($remember) {
            Authsome::persist('2 weeks');
        }

        if ($maintainer) {
            $this->Session->setFlash(__('You have been logged in', true));
            $this->redirect(array('controller' => 'users', 'action' => 'dashboard'));
        }
    }

/**
 * Logs the user out of the application
 */
    function logout() {
        $this->_logout();
    }

/**
 * Allows the user to queue up a forgot password job
 *
 * @todo Test this functionality thoroughly
 */
    function forgot_password() {
        if (!empty($this->data)) {
            try {
                if ($this->Maintainer->forgotPassword($this->data)) {
                    $this->Session->setFlash(__('An email has been sent with instructions for resetting your password', true));
                    $this->redirect(array('controller' => 'users', 'action' => 'login'));
                } else {
                    $this->Session->setFlash(__('An error occurred', true));
                    $this->log("Error sending email");
                }

            } catch (Exception $e) {
                $this->_flashAndRedirect($e->getMessage(), array('controller' => 'users', 'action' => 'forgot_password'));
            }
        }
    }

/**
 * Allows the user to reset their password based on a link generated via email
 *
 * @param string $username username
 * @param string $key unique key
 */
    function reset_password($username = null, $key = null) {
        if ($username == null || $key == null) {
            $this->Session->setFlash(__('An error occurred', true));
            $this->redirect(array('action' => 'login'));
        }

        $maintainer = $this->Maintainer->find('resetpassword', array('username' => $username, 'key' => $key));
        if (!isset($maintainer)) {
            $this->Session->setFlash(__('An error occurred', true));
            $this->redirect(array('controller' => 'users', 'action' => 'login'));
        }

        if (!empty($this->data) && isset($this->data['Maintainer']['password'])) {
            if ($this->Maintainer->save($this->data, array('fields' => array('id', 'password', 'activation_key'), 'callback' => 'reset_password', 'user_id' => $maintainer['Maintainer']['id']))) {
                $this->Session->setFlash(__('Your password has been reset successfully', true));
                $this->redirect(array('controller' => 'users', 'action' => 'login'));
            } else {
                $this->Session->setFlash(__('An error occurred please try again', true));
            }
        }

        $this->set(compact('maintainer', 'username', 'key'));
    }

/**
 * Change Password functionality
 */
    function change_password() {
        if (!empty($this->data)) {
            if ($this->Maintainer->save($this->data, array('fieldList' => array('id', 'password'), 'callback' => 'change_password'))) {
                $this->Session->setFlash(__('Your password has been successfully changed', true));
                $this->redirect(array('action' => 'dashboard'));
            } else {
                $this->Session->setFlash(__('Your password could not be changed', true));
            }
        }
    }

}