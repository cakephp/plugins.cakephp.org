<?php
App::uses('Environment', 'Lib');

Environment::configure('production',
	array(
		'server' => array('cakepackages.com')
	),
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

		'debug'                   => 0,
		'Routing.prefixes'        => array('admin', 'one'),
		'Security.salt'           => 'DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi',
		'Security.cipherSeed'     => '76859309657453542496749683645',

		'Favorites'               => array(
			'types'                  => array('bookmark' => 'Package'),
			'defaultTexts'           => array('bookmark' => __('Bookmark')),
			'modelCategories'        => array('Package'),
		),
		'Category'                => array(
			'sluggable'              => array('separator' => '-'),
		),

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

Environment::configure('staging',
	array(
		'server' => array('dev.cakepackages.com', 'staging.cakepackages.com')
	),
	array(
		// Site specific items
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

		// App Specific functions
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

		'Favorites'               => array(
			'types'                  => array('bookmark' => 'Package'),
			'defaultTexts'           => array('bookmark' => __('Bookmark')),
			'modelCategories'        => array('Package'),
		),
		'Category'                => array(
			'sluggable'              => array('separator' => '-'),
		),

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

		'Favorites'               => array(
			'types'                  => array('bookmark' => 'Package'),
			'defaultTexts'           => array('bookmark' => __('Bookmark')),
			'modelCategories'        => array('Package'),
		),
		'Category'                => array(
			'sluggable'              => array('separator' => '-'),
		),

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

// run

Environment::start();
