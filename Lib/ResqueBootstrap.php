<?php
// From Console/cake.php
define('DS', DIRECTORY_SEPARATOR);

$ds = DIRECTORY_SEPARATOR;
$dispatcher = 'Cake' . $ds . 'Console' . $ds . 'ShellDispatcher.php';
$root = dirname(dirname(dirname(__FILE__)));
$appDir = basename(dirname(dirname(__FILE__)));
$cakeCoreIncludePath = $root . $ds . $appDir . $ds . 'Vendor' . $ds . 'cakephp' . $ds . 'cakephp' . $ds . 'lib';

if (!defined('CAKE_CORE_INCLUDE_PATH')) {
	define('CAKE_CORE_INCLUDE_PATH', $cakeCoreIncludePath);
	define('CAKEPHP_SHELL', true);
	if (!defined('DS')) {
		define('DS', DIRECTORY_SEPARATOR);
	}
	if (!defined('CORE_PATH')) {
		define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
	}
}

if (function_exists('ini_set')) {
	// the following line differs from its sibling
	// /lib/Cake/Console/Templates/skel/Console/cake.php
	ini_set('include_path', $cakeCoreIncludePath . PATH_SEPARATOR . ini_get('include_path'));
}

if (!include $dispatcher) {
	trigger_error('Could not locate CakePHP core files.', E_USER_ERROR);
}

// Override tmp dir for CLI
define('TMP', dirname(__DIR__) . DS . 'tmp' . DS . 'cli' . DS);
// Ensure all tmp paths are created for cli requests
@mkdir(TMP);
$dirs = array(
	'cache', 'logs', 'tests',
	'cache' . DS . 'data',
	'cache' . DS . 'debug_kit',
	'cache' . DS . 'models',
	'cache' . DS . 'persistent',
	'cache' . DS . 'views',
);
foreach ($dirs as $dir) {
	@mkdir(TMP . $dir);
}

unset($dispatcher, $root, $appDir, $ds, $cakeCoreIncludePath, $dirs);

// From ShellDipatcher::_boostrap

define('ROOT', dirname(dirname(dirname(__FILE__))));
define('APP_DIR', basename(dirname(dirname(__FILE__))));
define('APP', ROOT . DS . APP_DIR . DS);
define('WWW_ROOT', APP . 'webroot' . DS);
if (!is_dir(ROOT . DS . APP_DIR . DS . 'tmp')) {
	define('TMP', CAKE_CORE_INCLUDE_PATH . DS . 'Cake' . DS . 'Console' . DS . 'Templates' . DS . 'skel' . DS . 'tmp' . DS);
}
$boot = file_exists(ROOT . DS . APP_DIR . DS . 'Config' . DS . 'bootstrap.php');
require getenv('CAKE') . DS . 'bootstrap.php';

if (!file_exists(APP . 'Config' . DS . 'core.php')) {
	include_once CAKE_CORE_INCLUDE_PATH . DS . 'Cake' . DS . 'Console' . DS . 'Templates' . DS . 'skel' . DS . 'Config' . DS . 'core.php';
	App::build();
}
require_once CAKE . 'Console' . DS . 'ConsoleErrorHandler.php';
$ErrorHandler = new ConsoleErrorHandler();
set_exception_handler(array($ErrorHandler, 'handleException'));
set_error_handler(array($ErrorHandler, 'handleError'), Configure::read('Error.level'));

if (!defined('FULL_BASE_URL')) {
	define('FULL_BASE_URL', 'http://localhost');
}

// End ShellDispatcher

App::uses('Shell', 'Console');
