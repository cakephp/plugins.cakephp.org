<?php
App::uses('Environment', 'Lib');

// config

Environment::configure('production',
	array(
		'server' => array('cakepackages.com')
	),
	array(
		'UrlCache.pageFiles'      => true,
		'Settings.SiteTitle'      => 'cakepackages',
		'Settings.FULL_BASE_URL'  => 'http://cakepackages.com',

		'Email.username'          => 'info@cakepackages.com',
		'Email.password'          => 'password',
		'Email.test'              => 'info@cakepackages.com',
		'Email.from'              => 'info@cakepackages.com',

		'debug'                   => 0,
		'Routing.prefixes'        => array('one'),
		'Security.salt'           => 'DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi',
		'Security.cipherSeed'     => '76859309657453542496749683645',
	),
	function() {
		error_reporting(0);

		if (function_exists('apc_fetch') && Configure::read('debug') == 0) {
			Cache::config('default', array(
			'engine' => 'Apc', //[required]
				'duration' => 3600, //[optional]
				'probability' => 100, //[optional]
				'prefix' => 'DEFAULT_', //[optional]  prefix every cache file with this string
			));
			Cache::config('_cake_core_', array(
				'engine' => 'Apc', //[required]
				'duration' => 3600, //[optional]
				'probability' => 100, //[optional]
				'prefix' => '_cake_core_', //[optional]  prefix every cache file with this string
			));
			// Override the debug_lot cache as not doing so makes some Cache::write() calls use the File cache
			Cache::config('debug_kit', array(
				'engine' => 'Apc', //[required]
				'duration' => '+4 hours', //[optional]
				'probability' => 100, //[optional]
				'prefix' => 'DEBUG_KIT_', //[optional]  prefix every cache file with this string
			));
			Cache::config('QUERYCACHE', array(
				'engine' => 'Apc', //[required]
				'duration' => 100, //[optional]
				'probability' => 100, //[optional]
				'prefix' => 'QUERYCACHE_', //[optional]  prefix every cache file with this string
			));
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
		'Settings.SiteTitle'      => 'cakepackages',
		'Settings.FULL_BASE_URL'  => 'http://staging.cakepackages.com',

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
		'Routing.prefixes'        => array('one'),
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
	),
	function() {
		date_default_timezone_set('UTC');

		Cache::config('default', array('engine' => 'File'));
	}
);

Environment::configure('development',
	true,
	array(
		'UrlCache.pageFiles'      => true,
		'Settings.SiteTitle'      => 'CakePackages',
		'Settings.FULL_BASE_URL'  => 'http://cakepackages.dev',

		'Email.username'          => 'email@example.com',
		'Email.password'          => 'password',
		'Email.test'              => 'email@example.com',
		'Email.from'              => 'email@example.com',

		'logQueries'              => true,

		'debug'                   => 2,
		'Cache.disable'           => true,
		'Routing.prefixes'        => array('one'),
		'Security.salt'           => 'AYcG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9ab',
		'Security.cipherSeed'     => '76859364557429242496749683650',
		'Recaptcha.publicKey'     => '6LeyksQSAAAAAJdkmQB7vBtsP9kYY75rE1ebY7B5',
		'Recaptcha.privateKey'    => '6LeyksQSAAAAAEOJpZmWFHoBzgpSBtVlbDCDy6Uv',
	)
);

// run

Environment::start();
