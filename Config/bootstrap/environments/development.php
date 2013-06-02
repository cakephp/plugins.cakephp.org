<?php
Environment::configure('development',
	true,
	array(
		'UrlCache.pageFiles'      => true,
		'Settings.SiteTitle'      => 'Package Indexer',
		'Settings.FULL_BASE_URL'  => 'http://cakepackages.dev',


		'Disqus.disqus_shortname' => 'cakepackages',
		'Disqus.disqus_developer' => 1,

		'Email.username'          => 'email@example.com',
		'Email.password'          => 'password',
		'Email.test'              => 'email@example.com',
		'Email.from'              => 'email@example.com',
		'logQueries'              => true,

		'debug'                   => 2,
		'Cache.disable'           => true,
		'Routing.prefixes'        => array('admin', 'one'),
		'Security.salt'           => 'AYcG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9ab',
		'Security.cipherSeed'     => '76859364557429242496749683650',

		'Favorites.types'         => array('bookmark' => 'Package'),
		'Favorites.defaultTexts'  => array('bookmark' => __('Bookmark')),
		'Favorites.modelCategories'=>array('Package'),

		'Category.sluggable'      => array('separator' => '-'),

		'ResqueOverrides.Redis.host'      => 'localhost',
		'ResqueOverrides.Redis.port'      => 6379,
		'ResqueOverrides.Redis.database'  => 0,
		'ResqueOverrides.Redis.namespace' => 'resque',
		'ResqueOverrides.Worker.queue'    => 'default',
		'ResqueOverrides.Worker.interval' => 5,
		'ResqueOverrides.Worker.workers'  => 1,
		'ResqueOverrides.Worker.log'      => TMP . 'logs' . DS . 'resque-worker.log',
		'ResqueOverrides.environment_variables' => array('CAKE_ENV'),
		'ResqueOverrides.Queues'          => array(
																							array('queue' => 'default'),
																							array('queue' => 'email', 'interval' => 5)
																						),
		'ResqueOverrides.Resque.lib'      => 'kamisama/php-resque-ex',
		'ResqueOverrides.Log.handler'     => 'RotatingFile',
		'ResqueOverrides.Log.target'      => TMP . 'logs' . DS . 'resque-error.log',

		// Feature flags
		'Feature.auth_required'   => true,
	),
	function() {
		date_default_timezone_set('UTC');
		if (!defined('FULL_BASE_URL')) {
			define('FULL_BASE_URL', Configure::read('Settings.FULL_BASE_URL'));
		}
	}
);
