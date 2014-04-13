<?php

Environment::configure('staging',
	array(
		'server' => array('staging.example.com')
	),
	array(
		// Site specific items
		'Settings.FULL_BASE_URL' => 'http://staging.example.com',

		'Email.username' => 'email@example.com',
		'Email.password' => 'password',
		'Email.test' => 'email@example.com',
		'Email.from' => 'email@example.com',

		'logQueries' => true,

		// App Specific functions
		'debug' => 0,

		// Securty
		'Security.level' => 'medium',
		'Security.salt' => 'SALT',
		'Security.cipherSeed' => 'CIPHERSEED',
	),
	function() {
		date_default_timezone_set('UTC');

		Cache::config('default', array('engine' => 'File'));
		if (!defined('FULL_BASE_URL')) {
			define('FULL_BASE_URL', Configure::read('Settings.FULL_BASE_URL'));
		}
	}
);
