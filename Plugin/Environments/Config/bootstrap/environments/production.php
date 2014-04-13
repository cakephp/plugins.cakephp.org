<?php

Environment::configure('production',
	array(
		'server' => array('example.com')
	),
	array(
		'Settings.FULL_BASE_URL' => 'http://example.com',

		// Email settings (maybe deprecated in 2.x)
		'Email.username' => 'email@example.com',
		'Email.password' => 'password',
		'Email.test' => 'email@example.com',
		'Email.from' => 'email@example.com',

		// Debug should be off in production
		'debug' => 0,

		// Securty
		'Security.level' => 'medium',
		'Security.salt' => 'SALT',
		'Security.cipherSeed' => 'CIPHERSEED',
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

		if (!defined('FULL_BASE_URL')) {
			define('FULL_BASE_URL', Configure::read('Settings.FULL_BASE_URL'));
		}
	}
);
