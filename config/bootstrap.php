<?php
/**
 * This file is loaded automatically by the app/webroot/index.php file after the core bootstrap.php
 *
 * This is an application wide file to load any function that is not used within a class
 * define. You can also use this to include or require any files in your application.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.app.config
 * @since         CakePHP(tm) v 0.10.8.2117
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * The settings below can be used to set additional paths to models, views and controllers.
 * This is related to Ticket #470 (https://trac.cakephp.org/ticket/470)
 *
 * App::build(array(
 *     'plugins' => array('/full/path/to/plugins/', '/next/full/path/to/plugins/'),
 *     'models' =>  array('/full/path/to/models/', '/next/full/path/to/models/'),
 *     'views' => array('/full/path/to/views/', '/next/full/path/to/views/'),
 *     'controllers' => array('/full/path/to/controllers/', '/next/full/path/to/controllers/'),
 *     'datasources' => array('/full/path/to/datasources/', '/next/full/path/to/datasources/'),
 *     'behaviors' => array('/full/path/to/behaviors/', '/next/full/path/to/behaviors/'),
 *     'components' => array('/full/path/to/components/', '/next/full/path/to/components/'),
 *     'helpers' => array('/full/path/to/helpers/', '/next/full/path/to/helpers/'),
 *     'vendors' => array('/full/path/to/vendors/', '/next/full/path/to/vendors/'),
 *     'shells' => array('/full/path/to/shells/', '/next/full/path/to/shells/'),
 *     'locales' => array('/full/path/to/locale/', '/next/full/path/to/locale/')
 * ));
 *
 */

/**
 * As of 1.3, additional rules for the inflector are added below
 *
 * Inflector::rules('singular', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 * Inflector::rules('plural', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 *
 */

function diebug($var = false, $showHtml = false, $showFrom = true) {
	if (Configure::read() > 0) {
		if ($showFrom) {
			$calledFrom = debug_backtrace();
			echo '<strong>' . substr(str_replace(ROOT, '', $calledFrom[0]['file']), 1) . '</strong>';
			echo ' (line <strong>' . $calledFrom[0]['line'] . '</strong>)';
		}
		echo "\n<pre class=\"cake-debug\">\n";

		$var = print_nice($var, true);
		if ($showHtml) {
			$var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
		}
		echo $var . "\n</pre>\n";
		die;
	}
}
function print_nice($elem, $max_level = 10, $print_nice_stack = array()){
	if (is_array($elem) || is_object($elem)) {
		if (in_array(&$elem, $print_nice_stack, true)) {
			echo "<span style='color:#f00;'>RECURSION</span>";
			return;
		}
		$print_nice_stack[] = &$elem;
		if ($max_level<1) {
			echo "<span style='color:#f00;'>max recursion level reached</span>";
			return;
		}
		$max_level--;
		echo "<table border=1 cellspacing=0 cellpadding=3 width=100%>";
		if (is_array($elem)) {
			echo '<tr><td colspan=2 style="background-color:#333;"><span style="color:#fff;font-weight:bold">ARRAY</span></td></tr>';
		} else {
			echo '<tr><td colspan=2 style="background-color:#333;"><span style="color:#fff;font-weight:bold">OBJECT Type: ' . get_class($elem) . '</span></td></tr>';
		}
		$color = 0;
		foreach ($elem as $k => $v) {
			if ($max_level%2) {
				$rgb = ($color++%2) ? "#888" : "#bbb";
			} else {
				$rgb = ($color++%2) ? "#88b" : "#bbf";
			}
			echo "<tr><td valign='top' style='background-color:{$rgb};font-weight:bold;width:40px;'>{$k}</td><td>";
			print_nice($v, $max_level, $print_nice_stack);
			echo "</td></tr>";
		}
		echo "</table>";
		return;
	}
	if ($elem === null) {
		echo "<span style='color:#000;font-style:italic;'>NULL</span>";
	} elseif ($elem === 0) {
		echo "0";
	} elseif ($elem === true) {
		echo "<span style='color:#060;font-weight:bold;'>TRUE</span>";
	} elseif ($elem === false) {
		echo "<span style='color:#c00000;font-weight:bold;'>FALSE</span>";
	} elseif ($elem === "") {
		echo "<span style='color:#000;'>EMPTY STRING</span>";
	} elseif (is_string($elem)) {
		echo "<span style='color:#000;'>string</span><br /><span style='color:#060;font-weight:bold;'>{$elem}</span>";
	} else {
		echo str_replace("\n","<strong><font color=red>*</font></strong><br>\n",$elem);
	}
}
?>