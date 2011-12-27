<?php

App::uses('Permit', 'Sanction.Controller/Component');

Permit::access(
	array('prefix' => 'admin'),
	array('auth' => array('group' => 'admin')),
	array(
		'element' => 'flash/error',
		'message' => __('Sorry, but you need to be an administrator to access this location.'),
		'redirect' => array('controller' => 'packages', 'action' => 'index'),
	)
);

Permit::access(
	array('controller' => 'github'),
	array('auth' => array('group' => 'admin')),
	array(
		'element' => 'flash/error',
		'message' => __('Sorry, but you need to be an administrator to access this location.'),
		'redirect' => array('controller' => 'packages', 'action' => 'index'),
	)
);

Permit::access(
	array('controller' => array('maintainers', 'packages'), 'action' => array('add', 'edit', 'delete')),
	array('auth' => array('group' => 'admin')),
	array(
		'element' => 'flash/error',
		'message' => __('Sorry, but you need to be an administrator to access this location.'),
		'redirect' => array('action' => 'index'),
	)
);

Permit::access(
	array('plugin' => 'settings'),
	array('auth' => array('group' => 'admin')),
	array(
		'element' => 'flash/error',
		'message' => __('Sorry, but you need to be an administrator to access this location.'),
		'redirect' => array('controller' => 'packages', 'action' => 'index'),
	)
);

Permit::access(
	array('controller' => 'users', 'action' => array('change_password', 'dashboard', 'logout')),
	array('auth' => true),
	array(
		'element' => 'flash/error',
		'message' => __('Sorry, but you need to be logged in to access this location.'),
		'redirect' => array('controller' => 'users', 'action' => 'login'),
	)
);

Permit::access(
	array('controller' => 'packages', 'action' => array('rate')),
	array('auth' => true),
	array(
		'element' => 'flash/error',
		'message' => __('Sorry, but you need to be logged in to access this location.'),
		'redirect' => array('controller' => 'users', 'action' => 'login'),
	)
);

Permit::access(
	array('controller' => 'users', 'action' => array('forgot_password', 'login', 'reset_password')),
	array('auth' => false),
	array(
		'element' => 'flash/error',
		'message' => __('Sorry, but you need to be logged in to access this location.'),
		'redirect' => array('controller' => 'users', 'action' => 'dashboard'),
	)
);