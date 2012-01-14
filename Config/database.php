<?php
class DATABASE_CONFIG {
	public $default = array(
		'datasource'    => 'Database/Mysql',
		'persistent'    => false,
		'host'          => 'localhost',
		'login'         => 'user',
		'password'      => 'password',
		'database'      => 'cakepackages',
		'prefix'        => '',
		'encoding'      => 'utf8',
	);

	public $test = array(
		'database'      => 'test',
	);

	public $development = array(
		'datasource'    => 'Database/MysqlLog',
		'login'         => 'user',
		'password'      => 'password',
		'database'      => 'cakepackages',
	);

	public $staging = array(
		'login'         => 'cakepackages_staging',
		'password'      => 'cakepackages_sta',
		'database'      => 'cakepackages_sta',
	);

	public $production = array(
		'login'         => 'cakepackages',
		'password'      => 'cakepackages',
		'database'      => 'cakepackages',
	);

	public $test_cakeusers = array(
	);

	public $development_cakeusers = array(
	);

	public $staging_cakeusers = array(
	);

	public $production_cakeusers = array(
		'database'      => 'cakeusers',
	);

	public $github = array(
		'datasource'    => 'GithubSource',
		'host'          => 'github.com',
		'login'         => null,
		'password'      => null,
		'database'      => 'api/v2/json',
	);

	protected $_skip = array(
		'_skip', 'default', 'github', '_environments',
		'test_cakeusers', 'development_cakeusers',
		'staging_cakeusers', 'production_cakeusers',
	);

	protected $_environments = array(
		'development'   => array('development_cakeusers'),
		'staging'       => array('staging_cakeusers'),
		'production'    => array('production_cakeusers'),
		'test'          => array('test_cakeusers'),
	);

/**
 * Generates a connection based on the current environment
 *
 * Does not account for multiple connections in an environment, ie. MySQL and Redis
 *
 * @todo Support multiple in-environment connections
 */
	function __construct() {
		// once Environment has decided where we at, it will write the name into Configure.
		if ($environment = Configure::read('Environment.name')) {

			// Require that the environment have a database configuration
			if (!isset($this->{$environment})) {
				throw new RuntimeException(sprintf('Missing Database Configuration %s', $environment));
			}

			// Merge environment into defaults
			$this->default = array_merge($this->default, $this->{$environment});

			// Merge environment with the environment-specific configurations
			if (isset($this->_environments[$environment])) {
				foreach ($this->_environments[$environment] as $name) {
					$this->$name = array_merge($this->default, $this->$name);
				}
			}
		}

		// if everything above went smooth, $this->default now has the correct login info.
		foreach (get_object_vars($this) as $name => $config) {
			if (in_array($name, $this->_skip)) {
				continue;
			}

			$this->$name = array_merge($this->default, $config);
		}
	}

}
