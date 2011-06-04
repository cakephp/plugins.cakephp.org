<?php
class AppController extends Controller {

/**
 * Array containing the names of components this controller uses. Component names
 * should not contain the "Component" portion of the classname.
 *
 * Example: `var $components = array('Session', 'RequestHandler', 'Acl');`
 *
 * @var array
 * @access public
 * @link http://book.cakephp.org/view/961/components-helpers-and-uses
 */
    var $components = array(
        'Authsome.Authsome' => array('model' => 'Maintainer'),
        'Mail',
        'RequestHandler',
        'Sanction.Permit' => array(
            'check' => 'group',
            'path' => 'Maintainer.Maintainer'
        ),
        'Session',
        'Settings.Settings',
        'Webservice.Webservice',
    );

/**
 * An array containing the names of helpers this controller uses. The array elements should
 * not contain the "Helper" part of the classname.
 *
 * Example: `var $helpers = array('Html', 'Javascript', 'Time', 'Ajax');`
 *
 * @var mixed A single name as a string or a list of names as an array.
 * @access protected
 * @link http://book.cakephp.org/view/961/components-helpers-and-uses
 */
    var $helpers = array(
        'AssetCompress.AssetCompress',
        'Sanction.Clearance' => array(
            'check' => 'group',
            'path' => 'Maintainer.Maintainer'
        ),
    );

/**
 * Sets the view class to AutoHelper, which autoloads helpers when needed
 *
 * @var string
 * @access public
 */
    var $view = 'AutoHelper';

/**
 * Sets the default redirection array
 *
 * @var array
 */
    var $redirectTo = array('action' => 'index');

/**
 * Used to set a max for the pagination limit
 *
 * @var int
 */
    var $paginationMaxLimit = 25;


/**
 * Object constructor - Adds the Debugkit panel if in development mode
 *
 * @return void
 */
    function __construct() {
        if (Configure::read('debug')) {
            $this->components['DebugKit.Toolbar'] = array('panels' => array('Sanction.permit'));
        }

        parent::__construct();
    }

/**
 * Called before the controller action.
 *
 * Used to set a max for the pagination limit
 *
 * @access public
 */
    public function beforeFilter() {
        parent::beforeFilter();

        // Enforces an absolute limit of 25
        if (isset($this->passedArgs['limit'])) {
            $this->passedArgs['limit'] = min(
                $this->paginationMaxLimit,
                $this->passedArgs['limit']
            );
        }
    }

/**
 * Called after the controller action is run, but before the view is rendered.
 *
 * Used to set the useJsonNative parameter to true for Webservice requests
 *
 * @access public
 * @link http://book.cakephp.org/view/984/Callbacks
 */
    function beforeRender() {
        $this->params['useJsonNative'] = true;
    }

/**
 * Convenience method for logging a user out of the application completely
 *
 * @param mixed $redirect If false, do not redirect, else redirect to specified action
 * @return void
 * @access protected
 */
    function _logout($redirect = array('action' => 'login')) {
        $this->Authsome->logout();
        $this->Session->delete($this->Authsome->settings['model']);

        if ($redirect) {
            $this->redirect($redirect);
        }
    }

/**
 * Convenience method to perform both a flash and a redirect in one call
 *
 * @param string $message Message to display on redirect
 * @param mixed $url A string or array-based URL pointing to another location within the app,
 *     or an absolute URL
 * @return void
 * @access protected
 */
    function _flashAndRedirect($message = null, $redirectTo = array()) {
        if ($message == null) {
            $message = __('Access Error', true);
        }

        if ($message !== false) {
            $this->Session->setFlash($message);
        }

        if (is_array($redirectTo)) {
            $redirectTo = array_merge($this->redirectTo, $redirectTo);
        }

        $this->redirect($redirectTo);
    }

/**
 * Redirect to some url if a given piece of information evaluates to false
 *
 * @param mixed $data Data to evaluate
 * @param mixed $message Message to use when redirecting
 * @return void
 * @access protected
 */
    function _redirectUnless($data = null, $message = null) {
        if (empty($data)) {
            $redirectTo = array();
            $status = null;
            $exit = true;
            $element = 'flash/error';

            if (is_array($message)) {
                if (isset($message['redirectTo'])) $redirectTo = $message['redirectTo'];
                if (isset($message['status'])) $status = $message['status'];

                if (isset($message['exit'])) $exit = $message['exit'];
                if (isset($message['message'])) $message = $message['message'];
                if (isset($message['element'])) $element = $message['element'];
            }

            if ($message === null) {
                $message = __('Access Error', true);
            }

            if (is_array($redirectTo)) {
                $redirectTo = array_merge($this->redirectTo, $redirectTo);
            }

            if ($message !== false) {
                $this->Session->setFlash($message, $element);
            }

            $this->redirect($redirectTo, $status, $exit);
        }
    }

}