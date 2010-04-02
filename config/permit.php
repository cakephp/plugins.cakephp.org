<?php

App::import('Component', 'PermitComponent');

Permit::access(
	array('controller' => 'github'),
	array('auth' => array('group' => 'admin'),
	array('redirect' => array('controller' => 'packages', 'action' => 'index'))));

Permit::access(
	array('controller' => array('maintainers', 'packages', 'tags'), 'action' => array('add', 'edit', 'delete')),
	array('auth' => array('group' => 'admin')),
	array('redirect' => array('action' => 'index')));

Permit::access(
	array('plugin' => 'settings'),
	array('auth' => array('group' => 'admin')),
	array('redirect' => array('controller' => 'packages', 'action' => 'index')));

Permit::access(
	array('controller' => 'users', 'action' => array('change_password', 'dashboard', 'logout')),
	array('auth' => true),
	array('redirect' => array('controller' => 'users', 'action' => 'login')));

Permit::access(
	array('controller' => 'users', 'action' => array('forgot_password', 'login', 'reset_password')),
	array('auth' => false),
	array('redirect' => array('controller' => 'users', 'action' => 'dashboard')));

?>