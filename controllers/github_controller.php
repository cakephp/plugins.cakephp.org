<?php
class GithubController extends AppController {

/**
 * The name of this controller. Controller names are plural, named after the model they manipulate.
 *
 * @var string
 * @access public
 * @link http://book.cakephp.org/view/959/Controller-Attributes
 */
    var $name = 'Github';

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
    var $uses = array('Github', 'Maintainer');

/**
 * Called before the controller action.
 *
 * Detach the Softdeletable behavior from packages to prevent odd behavior
 * when attempting to search for them
 *
 * @access public
 * @link http://book.cakephp.org/view/984/Callbacks
 */
    function beforeFilter() {
        parent::beforeFilter();
        $this->Maintainer->Package->Behaviors->detach('Softdeletable');
    }

/**
 * Paginates a set of maintainers with related repository information attached
 */
    function index() {
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
 */
    function view($username = null) {
        $user = $this->Github->find('userShow', $username);
        $this->_redirectUnless($user, __('Invalid user', true));

        try {
            $existing = $this->Maintainer->find('existing', $username);
        } catch (Exception $e) {
            $this->_flashAndRedirect($e->getMessage());
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
 */
    function add_package($username = null, $package = null) {
        $this->_redirectUnless($username, __('Invalid user', true));
        $this->_redirectUnless($package, __('Invalid package', true));

        if ($this->Github->savePackage($username, $package)) {
            $this->Session->setFlash(sprintf(__('Code for %s saved!', true), $package), 'flash/success');
            $this->redirect(array('action' => 'view', $username));
        }

        $this->_flashAndRedirect(
            sprintf(__('Code for %s not saved!', true), $package),
            array('action' => 'view', $username)
        );
    }

/**
 * Allows the viewing of an arbitrary github user
 *
 * @param string $username Github username
 */
    function github($username = null) {
        $user = $this->Github->find('userShow', $username);
        $this->_redirectUnless($username, __('Invalid user', true));
        $this->set(compact('user'));
    }

}