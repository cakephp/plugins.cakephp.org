<?php
/**
 * Short description for file.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
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
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/views/pages/home.ctp)...
 */
	Router::parseExtensions('json', 'xml');

	Router::connect('/', array('controller' => 'packages', 'action' => 'home'));
	Router::connect('/home', array('controller' => 'packages', 'action' => 'home'));
	Router::connect('/posts/*', array('plugin' => 'blog', 'controller' => 'blog_posts', 'action' => 'index'));
	Router::connect('/post/*', array('plugin' => 'blog', 'controller' => 'blog_posts', 'action' => 'view'));
	Router::connect('/login', array('controller' => 'users', 'action' => 'login'));
	Router::connect('/logout', array('controller' => 'users', 'action' => 'logout'));
	Router::connect('/dashboard', array('controller' => 'users', 'action' => 'dashboard'));
	Router::connect('/forgot_password', array('controller' => 'users', 'action' => 'forgot_password'));
	Router::connect('/reset_password', array('controller' => 'users', 'action' => 'reset_password'));
	Router::connect('/change_password', array('controller' => 'users', 'action' => 'change_password'));
	Router::connect('/package/*', array('controller' => 'packages', 'action' => 'view'));
	Router::connect('/maintainer/edit/*', array('controller' => 'maintainers', 'action' => 'edit'));
	Router::connect('/maintainer/*', array('controller' => 'maintainers', 'action' => 'view'));
	Router::connect('/lost/*', array('controller' => 'lost', 'action' => 'index'));
	Router::connect('/package_search/page::page/*', array('controller' => 'packages', 'action' => 'search', 'type' => 'Package'));
	Router::connect('/package_search/:term/*', array('controller' => 'packages', 'action' => 'search', 'type' => 'Package'));
	Router::connect('/package_search/*', array('controller' => 'packages', 'action' => 'search', 'type' => 'Package'));

/**
 * ...and connect the rest of 'Pages' controller's urls.
 */
	Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));
?>