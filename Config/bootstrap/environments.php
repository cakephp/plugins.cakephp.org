<?php
CakePlugin::load('Environments');
App::uses('Environment', 'Environments.Lib');
if (!class_exists('Environment')) {
	throw new InternalErrorException;
}

include dirname(__FILE__) . DS . 'environments' . DS . 'production.php';
include dirname(__FILE__) . DS . 'environments' . DS . 'staging.php';
include dirname(__FILE__) . DS . 'environments' . DS . 'development.php';

Environment::start();

if (php_sapi_name() != 'cli') {
	header("X-ENV: " . Configure::read('Environment.name'));
}