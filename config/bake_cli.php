<?php
/**
 * Include all your custom bake events here
 *
 * EventManager::instance()->on('Bake.beforeRender.Controller.controller', function (Event $event) {
 *    // logic here
 * });
 */

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Utility\Hash;

EventManager::instance()->on('Bake.initialize', function (Event $event) {
    // code here
});
