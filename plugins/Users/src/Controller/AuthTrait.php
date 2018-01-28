<?php
namespace Users\Controller;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

/**
 * Enable Crud to catch MissingActionException and attempt to generate response
 * using Crud.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
trait AuthTrait
{
    /**
     * Configures the AuthComponent
     *
     * @return void
     */
    protected function loadAuthComponent()
    {
        $config = Configure::read('Users');
        $scope = [
            'Users.active' => true,
        ];
        if (!empty($config['requireEmailAuthentication'])) {
            $scope['Users.email_authenticated'] = true;
        }

        $this->loadComponent('Auth', [
            'authorize' => [
                'Controller'
            ],
            'flash' => [
                'element' => 'flash/error',
                'key' => 'auth',
            ],
            'loginAction' => $config['loginAction'],
            'loginRedirect' => $config['loginRedirect'],
            'logoutRedirect' => $config['logoutRedirect'],
            'authenticate' => [
                'all' => [
                    'fields' => $config['fields'],
                    'userModel' => $config['userModel'],
                    'scope' => $scope,
                ],
                'Form',
            ]
        ]);

        if ($this->request->getParam('prefix', '') !== 'admin') {
            $this->Auth->allow();
        }

        if ($config['trackLoginActivity']) {
            $this->Auth->getEventManager()->on('Auth.afterIdentify', function(Event $event, array $user){
                return $this->trackLoginActivity($event, $user);
            });
        }

        if ($config['trackLastActivity']) {
            $this->getEventManager()->on('Controller.initialize', function(Event $event) {
                return $this->trackLastActivity($event);
            });
        }
    }

    protected function trackLastActivity(Event $event)
    {
        if ($this->Auth->user() === null) {
            return;
        }

        $lastActivity = new Time();
        $this->request->session()->write('Auth.User.last_activity', $lastActivity);

        $fields = ['last_activity' => $lastActivity];
        $conditions = ['Users.id' => $this->Auth->user('id')];
        $table = TableRegistry::get(Configure::read('Users.userModel'));
        $table->updateAll($fields, $conditions);
    }

    protected function trackLoginActivity(Event $event, array $user)
    {
        if (empty($user['id'])) {
            throw new LogicException('Cannot update the last_login timestamp for an unspecified user id');
        }

        $lastLogin = new Time();
        $user['last_login'] = $lastLogin;
        $event->result = $user;

        $fields = ['last_login' => $lastLogin];
        $conditions = ['Users.id' => $user['id']];
        $table = TableRegistry::get(Configure::read('Users.userModel'));
        $table->updateAll($fields, $conditions);
    }
}
