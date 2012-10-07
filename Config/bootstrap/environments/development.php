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

		'Recaptcha.publicKey'     => '6LeyksQSAAAAAJdkmQB7vBtsP9kYY75rE1ebY7B5',
		'Recaptcha.privateKey'    => '6LeyksQSAAAAAEOJpZmWFHoBzgpSBtVlbDCDy6Uv',

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
		if (!defined('FULL_BASE_URL')) {
			define('FULL_BASE_URL', Configure::read('Settings.FULL_BASE_URL'));
		}
	}
);
