<?php
Environment::configure('production',
	array('server' => array('cakepackages.com')),
	array(
		'UrlCache.pageFiles'      => true,
		'Settings.SiteTitle'      => 'cakepackages',
		'Settings.FULL_BASE_URL'  => 'http://cakepackages.com',
		'Settings.theme'          => 'Csf',

		'Disqus.disqus_shortname' => 'cakepackages',
		'Disqus.disqus_developer' => 0,

		'Email.username'          => 'info@cakepackages.com',
		'Email.password'          => 'password',
		'Email.test'              => 'info@cakepackages.com',
		'Email.from'              => 'info@cakepackages.com',
		'logQueries'              => false,

		'debug'                   => 0,
		'Routing.prefixes'        => array('admin', 'one'),
		'Security.salt'           => 'DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi',
		'Security.cipherSeed'     => '76859309657453542496749683645',

		'Favorites.types'         => array('bookmark' => 'Package'),
		'Favorites.defaultTexts'  => array('bookmark' => __('Bookmark')),
		'Favorites.modelCategories'=>array('Package'),

		'Category.sluggable'      => array('separator' => '-'),

		'CakeResqueOverrides.Redis.host'      => 'localhost',
		'CakeResqueOverrides.Redis.port'      => 6379,
		'CakeResqueOverrides.Redis.database'  => 0,
		'CakeResqueOverrides.Redis.namespace' => 'resque',
		'CakeResqueOverrides.Worker.queue'    => 'default',
		'CakeResqueOverrides.Worker.interval' => 5,
		'CakeResqueOverrides.Worker.workers'  => 1,
		'CakeResqueOverrides.Worker.log'      => TMP . 'logs' . DS . 'resque-worker.log',
		'CakeResqueOverrides.Env'             => array('CAKE_ENV'),
		'CakeResqueOverrides.Queues'          => array(
																							array('queue' => 'default'),
																							array('queue' => 'email', 'interval' => 5)
																						),
		'CakeResqueOverrides.Resque.lib'      => 'kamisama/php-resque-ex',
		'CakeResqueOverrides.Log.handler'     => 'RotatingFile',
		'CakeResqueOverrides.Log.target'      => TMP . 'logs' . DS . 'resque-error.log',


		// Feature flags
		'Feature.auth_required'   => false,
	),
	function() {
		error_reporting(0);
		date_default_timezone_set('UTC');

		if (function_exists('apc_fetch') && Configure::read('debug') == 0) {
			Cache::config('default', array(
				'engine' => 'Apc', //[required]
				'duration' => 3600, //[optional]
				'probability' => 100, //[optional]
				'prefix' => 'DEFAULT_', //[optional]  prefix every cache file with this string
			));
		}

		if (!defined('FULL_BASE_URL')) {
			define('FULL_BASE_URL', Configure::read('Settings.FULL_BASE_URL'));
		}
	}
);
