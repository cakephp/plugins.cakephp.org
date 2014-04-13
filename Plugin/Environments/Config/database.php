<?php
class DATABASE_CONFIG {

	public $default;

/**
 * Read the connection info from the environment
 **/
	public function __construct() {
		$this->default = array(
			'datasource' => 'Database/Mysql',
			'persistent' => false,
			'host' => $this->read('MYSQL_DB_HOST'),
			'login' => $this->read('MYSQL_USERNAME'),
			'password' => $this->read('MYSQL_PASSWORD'),
			'database' => $this->read('MYSQL_DB_NAME'),
			'prefix' => $this->read('MYSQL_PREFIX'),
			'encoding' => 'utf8',
		);
	}

/**
 * Allows reading of a key from env() or Configure::read() as appropriate
 *
 * @param $key key being read
 * @param $default default value in case env() and Configure::read() fail
 **/
	public function read($key, $default = null) {
		$value = env($key);
		if ($value !== null) {
			return $value;
		}

		$value = Configure::read($key);
		if ($value !== null) {
			return $value;
		}

		return $default;
	}

}
