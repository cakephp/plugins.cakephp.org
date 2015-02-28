<?php
App::uses('AppController', 'Controller');

class GithubController extends AppController
{

/**
 * An array containing the class names of models this controller uses.
 *
 * Example: `var $uses = array('Product', 'Post', 'Comment');`
 *
 * Can be set to array() to use no models.  Can be set to false to
 * use no models and prevent the merging of $uses with AppController
 *
 * @var mixed A single name as a string or a list of names as an array.
 * @link http://book.cakephp.org/view/961/components-helpers-and-uses
 */
    public $uses = array('Github', 'Maintainer');

    public $helpers = array('Github');

/**
 * Called before the controller action.
 *
 * Detach the SoftDeletable behavior from packages to prevent odd behavior
 * when attempting to search for them
 *
 * @link http://book.cakephp.org/view/984/Callbacks
 * @return void
 */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Maintainer->Package->Behaviors->detach('SoftDeletable');
    }

/**
 * Paginates a set of maintainers with related repository information attached
 *
 * @return void
 */
    public function index()
    {
        $this->paginate = array('limit' => 2);
        $maintainers = $this->Github->get(
            'relatedRepositories',
            $this->paginate('Maintainer')
        );
        $this->set(compact('maintainers'));
    }

/**
 * Allows access to viewing a specific user with their
 * github information inline
 *
 * @param string $username Github username
 * @return void
 */
    public function view($username = null)
    {
        $user = $this->Github->find('user', array('user' => $username));
        $this->_redirectUnless($user, __('Invalid user'));

        try {
            $existing = $this->Maintainer->find('existing', $username);
        } catch (Exception $e) {
            $this->Session->setFlash($e->getMessage(), 'flash/error');
            $this->redirect($this->redirectTo);
        }

        $repositories = $this->Github->get('newRepositories', $username);
        $this->set(compact('existing', 'repositories', 'user'));
    }

/**
 * Allows adding a github user's package to the index
 *
 * Note that the user must already exist in the index for this to succeed
 *
 * Repository must be public in order to be added
 *
 * @param string $username Github username
 * @param string $package Name of repository belonging to user
 * @return void
 */
    public function add_package($username = null, $package = null)
    {
        $this->_redirectUnless($username, __('Invalid user'));
        $this->_redirectUnless($package, __('Invalid package'));

        if ($this->Github->savePackage($username, $package)) {
            $this->Session->setFlash(sprintf(__('Code for %s saved!'), $package), 'flash/success');
            $this->redirect(array('action' => 'view', $username));
        }

        $this->Session->setFlash(sprintf(__('Code for %s not saved!'), $package), 'flash/error');
        $this->redirect(array('action' => 'view', $username));
    }

/**
 * Allows the viewing of an arbitrary github user
 *
 * @param string $username Github username
 * @return void
 */
    public function github($username = null)
    {
        $user = $this->Github->find('user', array('user' => $username));
        $this->_redirectUnless($username, __('Invalid user'));
        $this->set(compact('user'));
    }
}
