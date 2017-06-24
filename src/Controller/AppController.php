<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Crud\Controller\ControllerTrait;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    use ControllerTrait;

    /**
     * Whether or not to treat a controller as
     * if it were an admin controller or not.
     *
     * Used to turn CrudView on and off at a class-level
     *
     * @var bool
     */
    protected $isAdmin = false;

    /**
     * A list of actions where the CrudView.View
     * listener should be enabled. If an action is
     * in this list but `isAdmin` is false, the
     * action will still be rendered via CrudView.View
     *
     * @var array
     */
    protected $adminActions = [];

    /**
     * A list of actions that should be allowed for
     * authenticated users
     *
     * @var array
     */
    protected $allowedActions = [];

    /**
     * A list of actions where the Crud.SearchListener
     * and Search.PrgComponent should be enabled
     *
     * @var array
     */
    protected $searchActions = ['index', 'lookup'];

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

        $this->loadAuthComponent();
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'Crud.Index',
                'Crud.Add',
                'Crud.Edit',
                'Crud.View',
                'Crud.Delete',
            ],
            'listeners' => [
                'Crud.Api',
                'Crud.ApiPagination',
                'Crud.ApiQueryLog',
                'Crud.RelatedModels',
                'Crud.Redirect',
            ],
        ]);

        if ($this->isAdmin || in_array($this->request->action, $this->adminActions)) {
            $this->Crud->addListener('CrudView.View');
        }

        if (in_array($this->request->action, $this->searchActions) && $this->modelClass !== null) {
            list($plugin, $tableClass) = pluginSplit($this->modelClass);
            try {
                if ($this->$tableClass->behaviors()->hasMethod('filterParams')) {
                    $this->Crud->addListener('Crud.Search');
                    $this->loadComponent('Search.Prg', [
                        'actions' => $this->searchActions,
                    ]);
                }
            } catch (MissingModelException $e) {
            } catch (UnexpectedValueException $e) {
            }
        }

        /*
         * Enable the following components for recommended CakePHP security settings.
         * see http://book.cakephp.org/3.0/en/controllers/components/security.html
         */
        //$this->loadComponent('Security');
        //$this->loadComponent('Csrf');
    }

    /**
     * Before filter callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return void
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $this->Crud->on('beforePaginate', function (Event $event) {
            $repository = $event->subject()->query->repository();
            $primaryKey = $repository->primaryKey();

            if (!is_array($primaryKey)) {
                $this->paginate['order'] = [
                    sprintf('%s.%s', $repository->alias(), $primaryKey) => 'asc'
                ];
            }
        });

        if ($this->Crud->isActionMapped()) {
            $this->Crud->action()->config('scaffold.brand', Configure::read('App.name'));
        }

        $isRest = in_array($this->response->type(), ['application/json', 'application/xml']);
        $isAdmin = $this->isAdmin || in_array($this->request->action, $this->adminActions);
        if (!$isRest && $isAdmin) {
            $this->viewBuilder()->className('CrudView\View\CrudView');
        }
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return \Cake\Network\Response|null|void
     */
    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);
        $isRest = in_array($this->response->type(), ['application/json', 'application/xml']);

        if (!array_key_exists('_serialize', $this->viewVars) && $isRest) {
            $this->set('_serialize', true);
        }
    }

    /**
     * Configures the AuthComponent
     *
     * @return void
     */
    protected function loadAuthComponent()
    {
        $this->loadComponent('Auth', [
            'authorize' => ['Controller'],
            'flash' => [
                'element' => 'flash/error',
                'key' => 'auth',
            ],
            'loginAction' => [
                'plugin' => 'Users',
                'prefix' => false,
                'controller' => 'Users',
                'action' => 'login'
            ],
            'loginRedirect' => '/',
            'logoutRedirect' => '/',
            'authenticate' => [
                'all' => [
                    'fields' => ['username' => 'email', 'password' => 'passwd'],
                    'userModel' => 'Users',
                    'scope' => [
                        'User.email_authenticated' => 1,
                        'User.active' => 1,
                    ],
                ],
                'Form',
            ]
        ]);

        if (!isset($this->request->params['prefix']) || $this->request->params['prefix'] != 'admin') {
            $this->Auth->allow();
        }
    }

    /**
     * Check if the provided user is authorized for the request.
     *
     * @param array|\ArrayAccess|null $user The user to check the authorization of.
     *   If empty the user fetched from storage will be used.
     * @return bool True if $user is authorized, otherwise false
     */
    public function isAuthorized($user = null)
    {
        $action = $this->request->param('action');
        if (in_array($action, $this->allowedActions)) {
            return true;
        }

        return false;
    }
}
