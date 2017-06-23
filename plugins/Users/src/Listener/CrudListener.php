<?php
namespace Users\Listener;

use Cake\Controller\Controller;
use Cake\Controller\Exception\MissingComponentException;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\Exception\MissingBehaviorException;
use Cake\ORM\TableRegistry;
use Crud\Listener\BaseListener;
use Josegonzalez\CakeQueuesadilla\Traits\QueueTrait;

/**
 * Users Listener
 */
class CrudListener extends BaseListener
{
    use QueueTrait;

    /**
     * Constructor
     *
     * @param \Cake\Controller\Controller $Controller Controller instance
     * @param array $config Default settings
     */
    public function __construct(Controller $Controller, $config = [])
    {
        parent::__construct($Controller, $config);

        $authFields = $this->getFormAuthFields();
        $this->config('usernameField', $authFields['username']);
        $this->config('passwordField', $authFields['password']);
    }

    /**
     * Callbacks definition
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Crud.afterForgotPassword' => 'afterForgotPassword',
            'Crud.beforeHandle' => 'beforeHandle',
            'Crud.beforeRender' => 'beforeRender',
            'Crud.beforeSave' => 'beforeSave',
            'Crud.verifyToken' => 'verifyToken',
        ];
    }

    /**
     * After Forgot Password
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function afterForgotPassword(Event $event)
    {
        if (!$event->subject->success) {
            return;
        }

        $table = TableRegistry::get($this->_controller()->modelClass);
        if (!$table->behaviors()->has('Muffin/Tokenize.Tokenize')) {
            throw new MissingBehaviorException('Muffin/Tokenize.Tokenize');
        }

        $token = $table->tokenize($event->subject->entity->id);
        $this->push(['\App\Job\MailerJob', 'execute'], [
            'action' => 'forgotPassword',
            'mailer' => 'Users.User',
            'data' => [
                $this->config('usernameField') => $event->subject->entity->email,
                'token' => $token,
            ]
        ]);
    }

    /**
     * Before Verify
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function verifyToken(Event $event)
    {
        $event->subject->verified = TableRegistry::get('Muffin/Tokenize.Tokens')
            ->verify($event->subject->token);
    }

    /**
     * Before Handle
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeHandle(Event $event)
    {
        if ($event->subject->action === 'edit') {
            $this->beforeHandleEdit($event);

            return;
        }
        if ($event->subject->action === 'login') {
            $this->beforeHandleLogin($event);

            return;
        }
        if ($event->subject->action === 'forgotPassword') {
            $this->beforeHandleForgotPassword($event);

            return;
        }
        if ($event->subject->action === 'resetPassword') {
            $this->beforeHandleResetPassword($event);

            return;
        }
    }

    /**
     * Before Handle Edit Action
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeHandleEdit(Event $event)
    {
        $userId = $this->_controller()->Auth->user('id');
        $event->subject->args = [$userId];

        $this->_action()->saveOptions(['validate' => 'account']);
        $this->_action()->config('scaffold.page_title', 'Profile');
        $this->_action()->config('scaffold.disable_extra_buttons', true);
        $this->_action()->config('scaffold.viewblocks', [
            'actions' => ['' => 'text'],
        ]);

        $scaffoldFields = [
            $this->config('usernameField'),
            $this->config('passwordField') => [
                'required' => false,
            ],
            'confirm_password' => [
                'type' => 'password',
            ],
        ];
        if (Configure::read('Users.enableAvatarUploads') === true) {
            $scaffoldFields['avatar'] = ['type' => 'file'];
        }

        $this->_action()->config('scaffold.fields', $scaffoldFields);
    }

    /**
     * Before Handle ForgotPassword Action
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeHandleForgotPassword(Event $event)
    {
        $this->_controller()->set([
            'viewVar' => 'forgotPassword',
            'forgotPassword' => null,
        ]);
        $this->_controller()->viewBuilder()->template('add');
        $this->_action()->config('scaffold.page_title', 'Forgot Password?');
        $this->_action()->config('scaffold.fields', [
            $this->config('usernameField'),
        ]);
        $this->_action()->config('scaffold.viewblocks', [
            'actions' => ['' => 'text'],
        ]);
        $this->_action()->config('scaffold.disable_extra_buttons', true);
        $this->_action()->config('scaffold.submit_button_text', 'Send Password Reset Email');
    }

    /**
     * Before Handle Login Action
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeHandleLogin(Event $event)
    {
        $this->_controller()->set([
            'viewVar' => 'login',
            'login' => null,
        ]);
        $this->_controller()->viewBuilder()->template('add');
        $this->_action()->config('scaffold.page_title', 'Login');
        $this->_action()->config('scaffold.fields', [
            $this->config('usernameField'),
            $this->config('passwordField'),
        ]);
        $this->_action()->config('scaffold.viewblocks', [
            'actions' => ['' => 'text'],
        ]);
        $this->_action()->config('scaffold.disable_extra_buttons', true);
        $this->_action()->config('scaffold.submit_button_text', 'Login');
    }

    /**
     * Before Handle ResetPassword Action
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeHandleResetPassword(Event $event)
    {
        $this->_controller()->set([
            'viewVar' => 'resetPassword',
            'resetPassword' => null,
        ]);
        $this->_controller()->viewBuilder()->template('add');
        $this->_action()->config('scaffold.page_title', 'Enter a new password to reset your account');
        $this->_action()->config('scaffold.fields', [
            $this->config('passwordField'),
        ]);
        $this->_action()->config('scaffold.viewblocks', [
            'actions' => ['' => 'text'],
        ]);
        $this->_action()->config('scaffold.disable_extra_buttons', true);
        $this->_action()->config('scaffold.submit_button_text', 'Reset Password');
    }

    /**
     * Before Render
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeRender(Event $event)
    {
        if ($this->_request()->action === 'edit') {
            $this->beforeRenderEdit($event);

            return;
        }
    }

    /**
     * Before Render Edit Action
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeRenderEdit(Event $event)
    {
        $event->subject->entity->unsetProperty($this->config('passwordField'));
    }

    /**
     * Before Save
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeSave(Event $event)
    {
        if ($this->_request()->action === 'edit') {
            $this->beforeSaveEdit($event);

            return;
        }
    }

    /**
     * Before Render Edit Action
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeSaveEdit(Event $event)
    {
        if ($event->subject->entity->confirm_password === '') {
            $event->subject->entity->unsetProperty($this->config('passwordField'));
            $event->subject->entity->dirty($this->config('passwordField'), false);
        }
    }

    /**
     * Returns an array containing the form auth fields
     *
     * @return array
     */
    protected function getFormAuthFields()
    {
        if (!$this->_controller()->components()->has('Auth')) {
            throw new MissingComponentException(['class' => 'Auth']);
        }

        $authenticate = $this->_controller()->Auth->getAuthenticate('Form');
        return $authenticate->config('fields');
    }
}
