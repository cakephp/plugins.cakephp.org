<?php
use ADmad\SocialAuth\Middleware\SocialAuthMiddleware;
use Cake\Core\Configure;
use Cake\Event\EventManager;

/*
 * Read configuration file and inject configuration into various
 * CakePHP classes.
 *
 * By default there is only one configuration file. It is often a good
 * idea to create multiple configuration files, and separate the configuration
 * that changes from configuration that does not. This makes deployment simpler.
 */
try {
    Configure::load('Users.app', 'default', true);
} catch (\Exception $e) {
    exit($e->getMessage() . "\n");
}

EventManager::instance()->on('Server.buildMiddleware', function ($event, $middleware) {
    $config = Configure::read('Users.social');
    if (empty($config['serviceConfig']['provider'])) {
        return;
    }

    if (empty($config['getUserCallback'])) {
        $config['getUserCallback'] = 'getUserFromSocialProfile';
    }

    $userModel = Configure::read('Users.userModel');
    if (empty($userModel)) {
        throw new LogicException('Configure value Users.userModel is empty');
    }
    $config['userModel'] = $userModel;

    $fields = Configure::read('Users.fields');
    if (empty($fields['username']) || empty($fields['password'])) {
        throw new LogicException('Configure value Users.fields is invalid');
    }
    $config['fields'] = $fields;

    $middleware->add(new SocialAuthMiddleware($config));
});
