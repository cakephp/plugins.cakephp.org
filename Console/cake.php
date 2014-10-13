#!/usr/bin/php -q
<?php
/**
 * Command-line code generation utility to automate programmer chores.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Console
 * @since         CakePHP(tm) v 2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$ds = DIRECTORY_SEPARATOR;
$dispatcher = 'Cake' . $ds . 'Console' . $ds . 'ShellDispatcher.php';
$root = dirname(dirname(dirname(__FILE__)));
$app_dir = basename(dirname(dirname(__FILE__)));
$cake_core_include_path = $root . $ds . $app_dir . $ds . 'Vendor' . $ds . 'cakephp' . $ds . 'cakephp' . $ds . 'lib';
if (!defined('CAKE_CORE_INCLUDE_PATH')) {
	define('CAKE_CORE_INCLUDE_PATH', $cake_core_include_path);
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
  ini_set('include_path', $cake_core_include_path . PATH_SEPARATOR . ini_get('include_path'));
}

if (!include $dispatcher) {
  trigger_error('Could not locate CakePHP core files.', E_USER_ERROR);
}
unset($paths, $path, $dispatcher, $root, $app_dir, $ds);

return ShellDispatcher::run($argv);
