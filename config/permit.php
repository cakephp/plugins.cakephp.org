<?php

App::import('Component', 'PermitComponent');

PermitComponent::access(
	array('controller' => 'github'),
	array('auth' => array('group' => 'admin'),
	array('redirect' => array('controller' => 'packages', 'action' => 'index'))));

PermitComponent::access(
	array('controller' => array('maintainers', 'packages'), 'action' => array('add', 'edit', 'delete')),
	array('auth' => array('group' => 'admin')),
	array('redirect' => array('action' => 'index')));

PermitComponent::access(
	array('plugin' => 'settings'),
	array('auth' => array('group' => 'admin')),
	array('redirect' => array('controller' => 'packages', 'action' => 'index')));

PermitComponent::access(
	array('controller' => 'users', 'action' => array('change_password', 'dashboard', 'logout')),
	array('auth' => true),
	array('redirect' => array('controller' => 'users', 'action' => 'login')));

PermitComponent::access(
	array('controller' => 'users', 'action' => array('forgot_password', 'login', 'reset_password')),
	array('auth' => false),
	array('redirect' => array('controller' => 'users', 'action' => 'dashboard')));

?>