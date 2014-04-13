<?php
CakePlugin::load('Environments');

App::uses('Environment', 'Environments.Lib');

include dirname(__FILE__) . DS . 'environments' . DS . 'production.php';
include dirname(__FILE__) . DS . 'environments' . DS . 'staging.php';
include dirname(__FILE__) . DS . 'environments' . DS . 'development.php';

// run

Environment::start();