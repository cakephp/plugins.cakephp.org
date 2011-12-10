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

	public $development = array(
		'datasource'    => 'Database/MysqlLog',
		'login'         => 'user',
		'password'      => 'password',
		'database'      => 'cakepackages',
	);
	
	public $cakeusers = array(
		'datasource'    => 'Database/Mysql',
		'login'         => 'user',
		'password'      => 'password',
		'database'      => 'cakeusers',
	);

	public $staging = array(
		'login'         => 'cakepackages_dev',
		'password'      => 'cakepackages_dev',
		'database'      => 'cakepackages_dev',
	);

	public $production = array(
		'login'         => 'cakepackages',
		'password'      => 'cakepackages',
		'database'      => 'cakepackages',
	);

	public $test = array(
		'database'      => 'test',
	);

	public $github = array(
		'datasource'    => 'Datasource/Github',
		'host'          => 'github.com',
		'login'         => null,
		'password'      => null,
		'database'      => 'api/v2/json',
	);

	protected $_skip = array(
		'_skip', 'default', 'github'
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

			// now i am gonna test if theres a property of the same name
			if (isset($this->{$environment})) {

				// if so, i then merge any options into $default.
				$this->default = array_merge($this->default, $this->{$environment});
			}
		}

		// if everything above went smooth, $this->default now has the correct login info.
		// Since we are using $default i dont need to change anything else in my app.
		foreach (get_object_vars($this) as $name => $config) {
			if (in_array($name, $this->_skip)) {
				continue;
			}

			$this->$name = array_merge($this->default, $config);
		}
	}

}
