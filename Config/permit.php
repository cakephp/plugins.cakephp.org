<?php

App::uses('Permit', 'Sanction.Controller/Component');

$element = 'flash/error';
$message = __('Access denied.');
$redirect = array('controller' => 'packages', 'action' => 'home');
if (Configure::read('Feature.auth_required')) {
	$message = __('Sorry, but you need to be logged in to access this location.');
	$redirect = array('controller' => 'users', 'action' => 'login');
} 

Permit::access(
	array('prefix' => 'admin'),
	array('auth' => array('group' => 'admin')),
	array(
		'element' => $element,
		'message' => __('Sorry, but you need to be an administrator to access this location.'),
		'redirect' => array('controller' => 'packages', 'action' => 'home'),
	)
);

Permit::access(
	array('controller' => 'github'),
	array('auth' => array('group' => 'admin')),
	array(
		'element' => $element,
		'message' => __('Sorry, but you need to be an administrator to access this location.'),
		'redirect' => array('controller' => 'packages', 'action' => 'home'),
	)
);

Permit::access(
	array('controller' => array('maintainers', 'packages'), 'action' => array('add', 'edit', 'delete')),
	array('auth' => array('group' => 'admin')),
	array(
		'element' => $element,
		'message' => __('Sorry, but you need to be an administrator to access this location.'),
		'redirect' => array('controller' => 'packages', 'action' => 'home'),
	)
);

Permit::access(
	array('plugin' => 'settings'),
	array('auth' => array('group' => 'admin')),
	array(
		'element' => $element,
		'message' => __('Sorry, but you need to be an administrator to access this location.'),
		'redirect' => array('controller' => 'packages', 'action' => 'home'),
	)
);

Permit::access(
	array('controller' => 'users', 'action' => array('change_password', 'dashboard', 'logout')),
	array('auth' => true),
	compact('element', 'message', 'redirect')
);

Permit::access(
	array('controller' => 'packages', 'action' => array('rate')),
	array('auth' => true),
	compact('element', 'message', 'redirect')
);

Permit::access(
	array('controller' => 'users', 'action' => array('forgot_password', 'login', 'reset_password')),
	array('auth' => false),
	compact('element', 'message', 'redirect')
);