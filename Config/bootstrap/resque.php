<?php
App::uses('Debugger', 'Utility');
App::uses('Hash', 'Utility');

// Output debug info as log in CLI
if (php_sapi_name() == 'cli') {
    Debugger::outputAs('log');
}

$cakeResque = Configure::read('Resque');
$cakeResqueOverrides = Configure::read('ResqueOverrides');

Configure::write('Resque', Hash::merge((array)$cakeResque, $cakeResqueOverrides));

CakePlugin::load(array(
    'Resque' => array('bootstrap' => true)
));

Configure::write('Resque', Hash::merge((array)$cakeResque, $cakeResqueOverrides));

require_once APP . 'Plugin' . DS . 'Resque' . DS . 'Vendor' . DS . 'php-resque' . DS . 'lib' . DS . 'Resque.php';
Resque::setBackend(Configure::read('Resque.Redis.host') . ':' . Configure::read('Resque.Redis.port'));
