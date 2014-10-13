<?php
include dirname(__FILE__) . DS . 'bootstrap' . DS . 'functions.php';
include dirname(__FILE__) . DS . 'bootstrap' . DS . 'environments.php';

CakePlugin::loadAll();

// Plugin specific configuration goes here
include dirname(__FILE__) . DS . 'bootstrap' . DS . 'resque.php';

Configure::write('Dispatcher.filters', array(
	'AssetDispatcher',
	'CacheDispatcher'
));

CakePlugin::load('AssetCompress', array('bootstrap' => true));

/**
 * Configures default file logging options
 */
App::uses('CakeLog', 'Log');
CakeLog::config('debug', array(
	'engine' => 'FileLog',
	'types' => array('notice', 'info', 'debug'),
	'file' => 'debug',
));
CakeLog::config('error', array(
	'engine' => 'FileLog',
	'types' => array('warning', 'error', 'critical', 'alert', 'emergency'),
	'file' => 'error',
));
