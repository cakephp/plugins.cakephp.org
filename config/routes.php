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

	Router::connect('/1/:action/*', array('controller' => 'api', 'one' => true));

	Router::connect('/', array('controller' => 'packages', 'action' => 'home'));
	
	Router::connect('/:id-:slug',
		array('controller' => 'packages', 'action' => 'show'),
		array('id' => '[0-9]+', 'slug' => '[\w_-]+')
	);

	// Route cached files
	Router::connect('/cache_css/*', array('plugin' => 'asset_compress', 'controller' => 'css_files', 'action' => 'get'));
	Router::connect('/cache_js/*', array('plugin' => 'asset_compress', 'controller' => 'js_files', 'action' => 'get'));

	Router::connect('/login', array('controller' => 'users', 'action' => 'login'));
	Router::connect('/logout', array('controller' => 'users', 'action' => 'logout'));
	Router::connect('/dashboard', array('controller' => 'users', 'action' => 'dashboard'));
	Router::connect('/package/*', array('controller' => 'packages', 'action' => 'view'));
	Router::connect('/maintainer/*', array('controller' => 'maintainers', 'action' => 'view'));
	Router::connect('/posts/*', array('plugin' => 'blog', 'controller' => 'blog_posts', 'action' => 'index'));
	Router::connect('/post/*', array('plugin' => 'blog', 'controller' => 'blog_posts', 'action' => 'view'));

	App::import('Lib', 'PageRoute.PageRoute');
	Router::connect('/:page', array('controller' => 'pages', 'action' => 'display'),
		array('routeClass' => 'PageRoute')
	);