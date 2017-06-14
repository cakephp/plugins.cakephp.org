<?php
namespace Users\Listener;

use Cake\Controller\Exception\MissingComponentException;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Exception\MissingBehaviorException;
use Cake\ORM\TableRegistry;

/**
 * Users Listener
 */
class ControllerListener implements EventListenerInterface
{
    /**
     * Callbacks definition
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Controller.initialize' => 'initialize',
        ];
    }

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize(Event $event)
    {
        $Controller = $event->getSubject();
        $table = TableRegistry::get($Controller->modelClass);
        if (!$table->behaviors()->has('Tokenize')) {
            throw new MissingBehaviorException(['class' => 'Tokenize']);
        }
        if (!$Controller->components()->has('Auth')) {
            throw new MissingComponentException(['class' => 'Auth']);
        }
        if (!$Controller->components()->has('Crud')) {
            throw new MissingComponentException(['class' => 'Crud.Crud']);
        }

        $Controller->Crud->mapAction('login', 'CrudUsers.Login');
        $Controller->Crud->mapAction('logout', 'CrudUsers.Logout');
        $Controller->Crud->mapAction('forgotPassword', 'CrudUsers.ForgotPassword');
        $Controller->Crud->mapAction('resetPassword', [
            'className' => 'CrudUsers.ResetPassword',
            'findMethod' => 'token',
        ]);
        $Controller->Crud->mapAction('verify', [
            'className' => 'CrudUsers.Verify',
            'findMethod' => 'token',
        ]);
        $Controller->Auth->allow(['forgotPassword', 'resetPassword', 'verify']);

        $Controller->Crud->action()->config('scaffold.sidebar_navigation', false);
        $Controller->Crud->action()->config('scaffold.site_title', Configure::read('App.name'));
    }
}
