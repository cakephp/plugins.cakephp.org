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
                'CrudView.View',
                'Crud.RelatedModels',
                'Crud.Redirect',
            ],
        ]);

        if (in_array($this->request->action, $this->searchActions)) {
            list($plugin, $tableClass) = pluginSplit($this->modelClass);
            if (!empty($this->$tableClass) && $this->$tableClass->behaviors()->hasMethod('filterParams')) {
                $this->Crud->addListener('Crud.Search');
                $this->loadComponent('Search.Prg');
            }
        }
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
        $isAdmin = $this->request->prefix == 'admin' || $this->isAdmin;
        if (!$isRest && $isAdmin) {
            $this->viewClass = 'CrudView\View\CrudView';
        }
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return void
     */
    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);
        $isRest = in_array($this->response->type(), ['application/json', 'application/xml']);

        if (!array_key_exists('_serialize', $this->viewVars) && $isRest) {
            $this->set('_serialize', true);
        }
    }
}
