<?php

	Configure::write('debug', 2);

	// Set the default ErrorHandler
	Configure::write('Error', array(
		'handler' => 'ErrorHandler::handleError',
		'level' => E_ALL & ~E_DEPRECATED,
		'trace' => true
	));
	// Set the default ExceptionHandler
	Configure::write('Exception', array(
		'handler' => 'ErrorHandler::handleException',
		'renderer' => 'ExceptionRenderer',
		'log' => true
	));
	// Force UTF-8 Codebase
	Configure::write('App.encoding', 'UTF-8');

	// Conditionally disable mod_rewrite
	if (php_sapi_name() != 'cli' && function_exists('apache_get_modules')) {
		if (!in_array('mod_rewrite', apache_get_modules())) {
			Configure::write('App.baseUrl', env('SCRIPT_NAME'));
		}
	}
	// Current router prefixes
	Configure::write('Routing.prefixes', array('admin', 'one'));

	// Enable app-wide caching
	//Configure::write('Cache.disable', true);

	// Enable cache action
	//Configure::write('Cache.check', true);

	// Log message type
	define('LOG_ERROR', 2);

	// Configure session management
	Configure::write('Session', array(
		'defaults' => 'php'
	));

/**
 * The level of CakePHP security.
 */
	Configure::write('Security.level', 'medium');

/**
 * A random string used in security hashing methods.
 */
	Configure::write('Security.salt', 'DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi');

/**
 * A random numeric string (digits only) used to encrypt/decrypt strings.
 */
	Configure::write('Security.cipherSeed', '76859309657453542496749683645');

	// Do not use the built-in cakephp asset caching
	Configure::write('Asset.timestamp', false);

	// Do not use the built-in cakephp css filters
	//Configure::write('Asset.filter.css', 'css.php');

	// Do not use the built-in cakephp js filters
	//Configure::write('Asset.filter.js', 'custom_javascript_output_filter.php');

/**
 * The classname and database used in CakePHP's
 * access control lists.
 */
	Configure::write('Acl.classname', 'DbAcl');
	Configure::write('Acl.database', 'default');

	// Force UTC on the server
	date_default_timezone_set('UTC');

/**
 * Pick the caching engine to use.  If APC is enabled use it.
 * If running via cli - apc is disabled by default. ensure it's available and enabled in this case
 *
 */
$engine = 'File';
if (extension_loaded('apc') && function_exists('apc_dec') && (php_sapi_name() !== 'cli' || ini_get('apc.enable_cli'))) {
	$engine = 'Apc';
}

// In development mode, caches should expire quickly.
$duration = '+999 days';
if (Configure::read('debug') >= 1) {
	$duration = '+10 seconds';
}

/**
 * Configure the cache used for general framework caching.  Path information,
 * object listings, and translation cache files are stored with this configuration.
 */
Cache::config('_cake_core_', array(
	'engine' => $engine,
	'prefix' => 'plugins_cake_core_',
	'path' => CACHE . 'persistent' . DS,
	'serialize' => ($engine === 'File'),
	'duration' => $duration
));

/**
 * Configure the cache for model and datasource caches.  This cache configuration
 * is used to store schema descriptions, and table listings in connections.
 */
Cache::config('_cake_model_', array(
	'engine' => $engine,
	'prefix' => 'plugins_cake_model_',
	'path' => CACHE . 'models' . DS,
	'serialize' => ($engine === 'File'),
	'duration' => $duration
));

Cache::config('debug_kit', array(
	'engine' => $engine,
	'prefix' => 'DEBUG_KIT_', //[optional]  prefix every cache file with this string
	'path' => CACHE . 'debug_kit' . DS,
	'serialize' => ($engine === 'File'),
	'duration' => $duration
));