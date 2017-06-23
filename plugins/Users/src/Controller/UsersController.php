<?php
namespace Users\Controller;

use Cake\Core\Configure;
use LogicException;
use Users\Controller\AppController;
use Users\Listener\ControllerListener;
use Users\Listener\CrudListener;

/**
 * Users Controller
 *
 * @property \Users\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * A list of actions where the CrudView.View
     * listener should be enabled. If an action is
     * in this list but `isAdmin` is false, the
     * action will still be rendered via CrudView.View
     *
     * @var array
     */
    protected $adminActions = [
        'edit',
        'login',
        'forgotPassword',
        'resetPassword',
    ];

    /**
     * A list of actions that should be allowed for
     * authenticated users
     *
     * @var array
     */
    protected $allowedActions = [
        'edit',
        'logout',
    ];

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $userModel = Configure::read('Users.userModel');
        if (empty($userModel)) {
            throw new LogicException('Configure value Users.userModel is empty');
        }

        $this->_setModelClass($userModel);
        $this->eventManager()->attach(new ControllerListener);
        $this->Crud->addListener('Users', CrudListener::class);
    }
}
