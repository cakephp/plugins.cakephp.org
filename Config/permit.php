<?php

App::uses('Permit', 'Sanction.Controller/Component');

$element = 'flash/error';
$message = __('Access denied.');
$adminMessage = __('Sorry, but you need to be an administrator to access this location.');
$redirect = $adminRedirect = $logoutRedirect = array(
	'admin' => false,
	'controller' => 'packages',
	'action' => 'home'
);
if (Configure::read('Feature.auth_required')) {
	$message = __('Sorry, but you need to be logged in to access this location.');
	$redirect = array('controller' => 'users', 'action' => 'login');
} 

Permit::access(
	array('prefix' => 'admin'),
	array('auth' => array('is_admin' => 1)),
	array(
		'element' => $element,
		'message' => $adminMessage,
		'redirect' => $adminRedirect,
	)
);

// Block access to every plugin in case people try to cut around application logic
Permit::access(
	array('plugin' => array('favorites', 'ratings', 'categories', 'settings')),
	array('auth' => array('is_admin' => 1)),
	array(
		'element' => $element,
		'message' => $adminMessage,
		'redirect' => $adminRedirect,
	)
);

Permit::access(
	array('controller' => 'github'),
	array('auth' => array('is_admin' => 1)),
	array(
		'element' => $element,
		'message' => $adminMessage,
		'redirect' => $adminRedirect,
	)
);

Permit::access(
	array('controller' => 'users', 'action' => array('change_password', 'dashboard', 'logout')),
	array('auth' => true),
	compact('element', 'message', 'redirect')
);

Permit::access(
	array('controller' => 'packages', 'action' => array('rate', 'bookmark')),
	array('auth' => true),
	compact('element', 'message', 'redirect')
);

Permit::access(
	array('controller' => 'users', 'action' => array('forgot_password', 'login', 'reset_password')),
	array('auth' => false),
	array(
		'element' => $element,
		'message' => __('Sorry, but you need to be logged out to access this location.'),
		'redirect' => $logoutRedirect,
	)
);