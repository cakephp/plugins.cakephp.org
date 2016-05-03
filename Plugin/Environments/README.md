[![Build Status](https://img.shields.io/travis/josegonzalez/cakephp-environments/master.svg?style=flat-square)](https://travis-ci.org/josegonzalez/cakephp-environments) 
[![Coverage Status](https://img.shields.io/coveralls/josegonzalez/cakephp-environments.svg?style=flat-square)](https://coveralls.io/r/josegonzalez/cakephp-environments?branch=master) 
[![Total Downloads](https://img.shields.io/packagist/dt/josegonzalez/cakephp-environments.svg?style=flat-square)](https://packagist.org/packages/josegonzalez/cakephp-environments) 
[![Latest Stable Version](https://img.shields.io/packagist/v/josegonzalez/cakephp-environments.svg?style=flat-square)](https://packagist.org/packages/josegonzalez/cakephp-environments) 
[![Documentation Status](https://readthedocs.org/projects/cakephp-environments/badge/?version=latest&style=flat-square)](https://readthedocs.org/projects/cakephp-environments/?badge=latest)
[![Gratipay](https://img.shields.io/gratipay/josegonzalez.svg?style=flat-square)](https://gratipay.com/~josegonzalez/)

# Environments Plugin

A plugin to handle environment-level configuration

## Requirements

* CakePHP 2.x

## Installation

_[Using [Composer](http://getcomposer.org/)]_

[View on Packagist](https://packagist.org/packages/josegonzalez/cakephp-environments), and copy the json snippet for the latest version into your project's `composer.json`. Eg, v. 1.0.0 would look like this:

```javascript
{
	"require": {
		"josegonzalez/cakephp-environments": "1.0.0"
	}
}
```

Because this plugin has the type `cakephp-plugin` set in it's own `composer.json`, composer knows to install it inside your `/Plugins` directory, rather than in the usual vendors file. It is recommended that you add `/Plugins/Environments` to your .gitignore file. (Why? [read this](http://getcomposer.org/doc/faqs/should-i-commit-the-dependencies-in-my-vendor-directory.md).)

_[Manual]_

* Download this: [https://github.com/josegonzalez/cakephp-environments/zipball/master](https://github.com/josegonzalez/cakephp-environments/zipball/master)
* Unzip that download.
* Copy the resulting folder to `app/Plugin`
* Rename the folder you just copied to `Environments`

_[GIT Submodule]_

In your app directory type:

	git submodule add git://github.com/josegonzalez/cakephp-environments.git Plugin/Environments
	git submodule init
	git submodule update

_[GIT Clone]_

In your plugin directory type

	git clone git://github.com/josegonzalez/cakephp-environments.git Environments

### Enable plugin

Before using, you MUST enable the plugin:

	CakePlugin::load('Environments');

If you are already using `CakePlugin::loadAll();` before usage, then this is not necessary.

## Usage

Setup a directory structure as follows:

	app/Config/bootstrap/environments.php
	app/Config/bootstrap/environments/production.php
	app/Config/bootstrap/environments/staging.php
	app/Config/bootstrap/environments/development.php

We specify multiple **environment** files to separate the various configurations and make it easy to inspect a given environment. This is not necessary, but useful if you are configuring a large amount of information.

Your `environments.php` should contain the following:

	<?php
	CakePlugin::load('Environments');
	App::uses('Environment', 'Environments.Lib');

	include dirname(__FILE__) . DS . 'environments' . DS . 'production.php';
	include dirname(__FILE__) . DS . 'environments' . DS . 'staging.php';
	include dirname(__FILE__) . DS . 'environments' . DS . 'development.php';

	Environment::start();

Then, you need to include the `environments.php` file you created in your application, for example in `bootstrap.php`:

	include dirname(__FILE__) . DS . 'bootstrap' . DS . 'environments.php';

This will:

- Load the environments plugin in cases where it may not already be loaded
- Include every environment, in order of specificity (development is usually last)
- Start the environment

### Environment Configuration

Each environment may be configured as follows:

	Environment::configure($name, $params, $config = null, $callable = null);

Example usage of the `Environment::configure()` call is available in the `Config/bootstrap/environments` folder of this plugin.

- `$name`: Usually something like `production` or `development`. Good practice is to name it the same as the file it was in, so that there isn't any confusion as to what a given env file contains
- `$params`: An array or boolean of environment variables used to check whether this environment applies. If a boolean and set to true, this environment is automatically initiated. A value of `true` should only be used for development:

		# If a previous environment is not enabled, this one will be set
		Environment::configure('development', true, array('debug' => true));

	If an array, the **keys** may be function names that are called, with the **values** being passed to it:

		Environment::configure('development', true, array(
			'some_function' => 'aValueForThisCheck',
			'another_function' => 'aDifferentValueForThisCheck',
		));

	In all other cases, we simply check the environment:

		Environment::configure('octo_development', true, array(
			'SERVER_NAME' => 'octo-example.dev',
		));

		Environment::configure('bear_development', true, array(
			'SERVER_NAME' => 'bear-example.dev',
		));

		Environment::configure('development', true, array(
			'CAKE_ENV' => 'development',
		));

	If we are running in CLI, the only check enforced is `CAKE_ENV`, and the value MUST be the name of the environment:

		CAKE_ENV=production Console/cake bake all

	If the `CAKE_ENV` environment variable is set at all, it takes precedence over everything, including a boolean value for `$params`.
- `$config`: If set, an array of `keys` => `values` that are set via `Configure::write()`. Useful for batch-setting api keys, database connection information, etc.
- `$callable`: A PHP callable, such as `array('ClassName', 'functionName')`. In PHP53+, it is also possible to use an anonymous function. This is the preferred method of setting information that might depend upon the value of some function call.

### Database Switching

You may wish to set database connection information based upon the current environment. You can set your `app/Config/database.php` to the following contents:

	<?php
	class DATABASE_CONFIG {

		public $default;

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

This way, the only place necessary to view connection information is in your configured environment. It is also possible to set the connection information via a `CLI` wrapper, `nginx` or `apache` configuration, or even in your cloud provider's env settings, such as Heroku or PHPFog.

## Todo

* Unit Tests

## License

Copyright (c) 2014 Jose Diaz-Gonzalez

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
