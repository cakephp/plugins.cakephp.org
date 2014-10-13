<?php
/**
 * Singleton class to handle environment specific configurations.
 *
 * Auto-detect environment based on specific configured params and
 * allow per environment configuration and environment emulation.
 *
 * Environment. Smart Environment Handling.
 * Copyright 2008 Rafael Bandeira - rafaelbandeira3
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 */
class Environment {

	public $environments = array();

	protected $_configMap = array(
		'security' => 'Security.level'
	);

	protected $_paramMap = array(
		'server' => 'SERVER_NAME'
	);

	public static function &getInstance() {
		static $instance = array();
		if (!isset($instance[0])) {
			$Environment = 'Environment';
			if (config('app_environment')) {
				$Environment = 'App' . $Environment;
			}
			$instance[0] = new $Environment();
			Configure::write('Environment.initialized', true);
		}
		return $instance[0];
	}

	public static function configure($name, $params, $config = null, $callable = null) {
		$_this = Environment::getInstance();
		$_this->environments[$name] = compact('name', 'params', 'config', 'callable');
	}

	public static function start($environment = null) {
		$_this =& Environment::getInstance();
		$_this->setup($environment);
	}

	public static function is($environment) {
		$_this =& Environment::getInstance();
		return ($_this->name === $environment);
	}

	public function __construct() {
		if (Configure::read('Environment.initialized')) {
			throw new Exception('Environment can only be initialized once');
		}
	}

	public function setup($environment = null) {
		if (Configure::read('Environment.setup')) {
			return;
		}

		$current = ($environment === null) ? 'development' : $environment;
		if (empty($environment)) {
			foreach ($this->environments as $name => $config) {
				if ($this->_cliMatch($name) || $this->_match($config['params'])) {
					$current = $name;
					break;
				}
			}
		}

		$config = array_merge(
			$this->environments[$current]['config'],
			array('Environment.name' => $current)
		);
		foreach ($config as $param => $value) {
			if (isset($this->_configMap[$param])) {
				$param = $this->_configMap[$param];
			}
			Configure::write($param, $value);
		}

		if (is_callable($this->environments[$current]['callable'])) {
			$this->environments[$current]['callable']();
		}

		if (Configure::read('debug') > 0) {
			App::uses('CakeLog', 'Log');
			if (class_exists('CakeLog')) {
				CakeLog::write(LOG_INFO, $current);
				Configure::write('Environment.setup', true);
			}
		}
	}

	protected function _cliMatch($environment) {
		if (php_sapi_name() != 'cli') {
			return false;
		}

		return (isset($_SERVER['CAKE_ENV']) && $_SERVER['CAKE_ENV'] == $environment);
	}

	protected function _match($params) {
		if ($params === true) {
			return true;
		}

		foreach ($params as $param => $value) {
			if (isset($this->_paramMap[$param])) {
				$param = $this->_paramMap[$param];
			}

			if (function_exists($param)) {
				$match = call_user_func($param, $value);
			} elseif (is_array($value)) {
				$match = in_array(env($param), $value);
			} else {
				$match = (env($param) === $value);
			}

			if (!$match) {
				return false;
			}
		}
		return true;
	}
}
