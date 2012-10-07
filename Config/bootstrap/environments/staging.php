<?php
Environment::configure('staging',
	array('server' => array('dev.cakepackages.com', 'staging.cakepackages.com')),
	array(
		'UrlCache.pageFiles'      => true,
		'Settings.SiteTitle'      => 'Package Indexer',
		'Settings.FULL_BASE_URL'  => 'http://staging.cakepackages.com',
		'Settings.theme'          => 'Csf',

		'Disqus.disqus_shortname' => 'cakepackages',
		'Disqus.disqus_developer' => 1,

		'Email.username'          => 'info@cakepackages.com',
		'Email.password'          => 'password',
		'Email.test'              => 'info@cakepackages.com',
		'Email.from'              => 'info@cakepackages.com',
		'logQueries'              => true,

		'debug'                   => 2,
		'log'                     => true,
		'App.encoding'            => 'UTF-8',
		'Cache.disable'           => true,
		'Routing.prefixes'        => array('admin', 'one'),
		'Session.save'            => 'php',
		'Session.cookie'          => 'CAKEPHP',
		'Session.timeout'         => '120',
		'Session.start'           =>  true,
		'Session.checkAgent'      =>  true,
		'Security.level'          => 'medium',
		'Security.salt'           => 'DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi',
		'Security.cipherSeed'     => '76859309657453542496749683645',
		'Acl.classname'           => 'DbAcl',
		'Acl.database'            => 'default',

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
		'Feature.auth_required'   => true,
	),
	function() {
		date_default_timezone_set('UTC');

		Cache::config('default', array('engine' => 'File'));
		if (!defined('FULL_BASE_URL')) {
			define('FULL_BASE_URL', Configure::read('Settings.FULL_BASE_URL'));
		}
	}
);
