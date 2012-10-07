<?php
App::uses('Hash', 'Utility');

// Output debug info as log in CLI
if (php_sapi_name() == 'cli') {
	Debugger::outputAs('log');
}

$cakeResque = Configure::read('CakeResque');
$cakeResqueOverrides = Configure::read('CakeResqueOverrides');

Configure::write('CakeResque', Hash::merge((array)$cakeResque, $cakeResqueOverrides));

CakePlugin::load(array(
	'CakeResque' => array('bootstrap' => true)
));

Configure::write('CakeResque', Hash::merge((array)$cakeResque, $cakeResqueOverrides));

require_once APP . 'Plugin' . DS . 'CakeResque' . DS . 'vendor' . DS . 'kamisama' . DS . 'php-resque-ex' . DS . 'lib' . DS . 'Resque.php';
Resque::setBackend(Configure::read('CakeResque.Redis.host') . ':' . Configure::read('CakeResque.Redis.port'));
