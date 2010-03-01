<?php
/**
 * Mi Cache - A vendor for wrapping calls for data that can/should be cached
 *
 * PHP version 5
 *
 * Copyright (c) 2009, Andy Dawson
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright (c) 2009, Andy Dawson
 * @link          www.ad7six.com
 * @package       base
 * @subpackage    base.vendors
 * @since         v 1.0 (02-Mar-2009)
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * MiCache class
 *
 * This vendor can be used to avoid writing code like this:
 *
 *	function someFunction($params = array()) {
 *		$data = cache('some_cache_path');
 *		if ($data) {
 *			$data = unserialize($data);
 *		} else {
 *			....
 *			$data = $this->find(...);
 *			...
 *			cache('some_cache_path', $data, '+forever');
 *		}
 *	}
 *
 * OR this view code:
 *	$data = cache('some_cache_path');
 *	if ($data) {
 *		$data = unserialize($data);
 *	} else {
 *		$data = $this->requestAction('/some/string/url/or/array/url', array('some' => 'params'));
 *		cache('some_cache_path', $data, '+forever');
 *	}
 *
 * Instead, wrap in a call to MiCache:: like so:
 *	$data = MiCache::data('ModelName', 'someFunction', $some, $more, $params);
 * OR:
 *	$data = MiCache::rA('/some/string/url/or/array/url', array('some' => 'params'));
 *
 * @uses          Object
 * @package       base
 * @subpackage    base.vendors
 */
class MiCache extends Object {

/**
 * setting property
 *
 * @var mixed null
 * @access public
 */
	public static $setting = null;

/**
 * settings property
 *
 * The active instance's settings.
 *
 * @var array
 * @access public
 */
	public static $settings = array();

/**
 * defaultSettings property
 *
 * MiCache by default will store data in:
 *	tmp/cache/data/a/b/abcdef0123456789..
 * Where a is the first char of the hash, b is the second and the whole hash is used as the filename
 *
 * name - the cache config name
 * engine - the engine to use, defaults to MiFile which permits cachekeys to contain folders
 * duration - how long should data be cached for
 * prefix - the prefix for the filename or partial path.
 * serialize - whether to serialize the cached data or not
 * dirLevels - how many dir levels to create
 * dirLength - how many characters of the hask-key to be used for each folder name
 *
 * @var array
 * @access public
 */
	public static $defaultSettings = array(
		'name' => 'mi_cache',
		'engine' => 'MiFile',
		'duration' => '+1 year',
		'prefix' => null,
		'path' => 'data/',
		'serialize' => true,
		'dirLevels' => 2,
		'dirLength' => 1
	);

/**
 * config method
 *
 * Configure the cache engine
 *
 * @param array $config array()
 * @return void
 * @access public
 */
	public static function config($config = array()) {
		if (!$config && MiCache::$setting) {
			return array(MiCache::$setting => MiCache::$settings[MiCache::$setting]);
		}
		$_defaults = MiCache::$defaultSettings;
		if (Configure::read()) {
			$_defaults['duration'] = 100;
		}
		$name = isset($config['name'])?$config['name']:'mi_cache';
		if (!MiCache::$setting) {
			MiCache::$setting = $name;
		}
		MiCache::$settings[$name] = am($_defaults, $config);
		if (MiCache::$settings[$name]['path'][0] != '/') {
			MiCache::$settings[$name]['path'] = CACHE . MiCache::$settings[$name]['path'];
		}
		if (in_array(MiCache::$settings[$name]['engine'], array('File', 'MiFile')) && !is_dir(MiCache::$settings[$name]['path'])) {
			new Folder(MiCache::$settings[$name]['path'], true);
		}
		Cache::config($name, MiCache::$settings[$name]);
		return array($name => MiCache::$settings[$name]);
	}

/**
 * clear method
 *
 * Delete everything in the cache
 *
 * @param bool $check false
 * @return void
 * @access public
 */
	function clear($check = false) {
		if (MiCache::$setting === null) {
			MiCache::config();
		}
		Cache::clear($check, MiCache::$setting);
	}

/**
 * delete method
 *
 * Call with the same params as you would for data - deletes the cached result
 *
 * @return void
 * @access public
 */
	function delete() {
		if (MiCache::$setting === null) {
			MiCache::config();
		}

		$cacheKey = MiCache::key(func_get_args());
		Cache::delete($cacheKey, MiCache::$setting);
	}

/**
 * mi method
 *
 * Same logic as the data function - but you can pass it the name of a class to call a function
 * statically, or an object.
 *
 * There's a shortcut for the Mi class such that:
 * MiCache::mi('models') is the same as MiCache::mi('Mi', 'models');
 *
 * Other Examples:
 * MiCache::mi($someobject, 'slowmethod', $params);
 * MiCache::mi('SomeClass', 'slowmethod', $params);
 *
 * @param mixed $name
 * @param mixed $func
 * @param array $params array()
 * @return void
 * @access public
 */
	function mi($name, $func = null) {
		if (MiCache::$setting === null) {
			MiCache::config();
		}

		$numArgs = func_num_args();
		if ($numArgs > 2) {
			$params = func_get_args();
			array_shift($params);
			array_shift($params);
		} else {
			$params = array();
		}
		if (MiCache::$setting === null) {
			MiCache::config();
		}
		App::import('Vendor', 'Mi.Mi');
		if ($func === null || !in_array($func, get_class_methods('Mi'))) {
			if ($numArgs > 2) {
				array_unshift($params, $func);
			} elseif ($func !== null) {
				$params[] = $func;
			}
			$func = $name;
			$name = 'Mi';
			include_once(dirname(__FILE__) . DS . 'mi.php');
			$args = array($name, $func, $params);
		} else {
			$args = func_get_args();
			if (is_object($args[0])) {
				if (isset($object->alias)) {
					$args[0] = $object->alias;
				} elseif (isset($object->name)) {
					$args[0] = $object->name;
				} else {
					$args[0] = get_class($args[0]);
				}
			}
		}
		$cacheKey = MiCache::key($args);
		$return = MiCache::_read($cacheKey, MiCache::$setting);
		if ($return !== null && !Configure::read('Cache.disable')) {
			return $return;
		}
		if (is_string($name)) {
			if (!class_exists($name)) {
				if (App::import('Vendor', $name)) {
					if (!class_exists($name)) {
						trigger__error('App::import succeeded but ' . $name . ' class still not found');
						return false;
					}
					// @TODO allow for possibilies other than vendors?
				} else {
					trigger_error($name . ' class not found - needs loading');
					return false;
				}
			}
		}
		$return = call_user_func_array(array($name, $func), $params);
		MiCache::write($cacheKey, $return, MiCache::$setting);
		return $return;
	}

/**
 * data method
 *
 * Retrieve cached data with a fallback to query any object you can access via the class registry. Use in preference
 * to creating requestAction-only controller functions which are just pass-thrus to existing model methods. Use with
 * care, the intention is to enable *data caching* not convert your Cake application into a MVC-pull implementation.
 *
 * Examples (equivalents)
 *	$recentPosts = MiCache::data('Post', 'recent', array('limit' => 5));
 *	$recentPosts = MiCache::data('Post', 'find', array('list', array('order' => 'created DESC', 'limit' => 5)));
 *
 * @param mixed $name
 * @param mixed $func
 * @param array $params array()
 * @return void
 * @access public
 */
	function data($name, $func) {
		if (MiCache::$setting === null) {
			MiCache::config();
		}

		if (func_num_args() > 2) {
			$params = func_get_args();
			array_shift($params);
			array_shift($params);
		} else {
			$params = array();
		}

		$cacheKey = MiCache::key(func_get_args());
		$return = MiCache::_read($cacheKey, MiCache::$setting);
		if ($return !== null && !Configure::read('Cache.disable')) {
			return $return;
		}
		$return = call_user_func_array(array(ClassRegistry::init($name), $func), $params);
		MiCache::write($cacheKey, $return, MiCache::$setting);
		return $return;
	}

/**
 * Convenience function
 *
 * @return void
 * @access public
 */
	public static function key($string = '') {
		if (MiCache::$setting === null) {
			MiCache::config();
		}

		if (count(func_get_args() > 1 || !is_string($string))) {
			$string = serialize(func_get_args());
		}
		$hash = md5(Configure::read('Config.language') . $string);
		$config = current(MiCache::config());
		$offset = $prefix = null;
		for ($i = 1; $i <= $config['dirLevels']; $i++) {
			$prefix .= substr($hash, $offset, $config['dirLength']) . DS;
			$offset = $i * $config['dirLength'];
		}
		return $prefix . $hash;
	}

/**
 * read method - directly query the cache
 *
 * @param mixed $cacheKey
 * @param mixed $setting null
 * @return void
 * @access public
 */
	public static function read($cacheKey, $setting = null) {
		if (MiCache::$setting === null) {
			MiCache::config();
		}

		if (!$setting) {
			$setting = MiCache::$setting;
		}
		return MiCache::_read($cacheKey, $setting);
	}

/**
 * setting method
 *
 * Query the application setting model, if there is no result query Configure::read - and cache
 * the result
 *
 * @param string $cacheKey ''
 * @param mixed $dummy null
 * @return void
 * @access public
 */
	public static function setting($cacheKey = '', $aroId = null) {
		if (MiCache::$setting === null) {
			MiCache::config();
		}

		$aroPrefix = '';
		if ($aroId) {
			$aroPrefix = $aroId . '-';
		}
		$return = MiCache::_read($aroPrefix . $cacheKey, MiCache::$setting);
		if ($return !== null) {
			return $return;
		}
		if ($Inst = MiCache::_init('MiSettings.Setting')) {
			$return = $Inst->data($cacheKey, $aroId);
			if ($return === null) {
				$return = Configure::read($cacheKey);
			}
			MiCache::write($aroPrefix . $cacheKey, $return, MiCache::$setting);
		}
		return $return;
	}

/**
 * Write directly to the configured cache
 *
 * @param mixed $cacheKey
 * @param mixed $data
 * @return void
 * @access public
 */
	public static function write($cacheKey, $data, $setting = null) {
		if (MiCache::$setting === null) {
			MiCache::config();
		}

		if (!$setting) {
			$setting = MiCache::$setting;
		}
		$settings = MiCache::$settings[$setting];
		$path = dirname($settings['path'] . $settings['prefix'] . $cacheKey);
		if (MiCache::_createDir($path)) {
			return Cache::write($cacheKey, $data, $setting);
		}
		return false;
	}

/**
 * createDir method
 *
 * @param mixed $path
 * @return void
 * @access protected
 */
	protected static function _createDir($path) {
		if (!is_dir($path)) {
			new Folder($path, true);
		}
		return is_writable($path);
	}

/**
 * Read from the cache. if the read value evaluates to false check if it's false
 * Because there's nothing in the cache for that key, OR if the stored value
 * is actually false
 *
 * @param mixed $key
 * @param mixed $cSettings
 * @return void
 * @access protected
 */
	protected static function _read($key, $cSettings) {
		$return = Cache::read($key, $cSettings);
		if ($return) {
			return $return;
		}
		$_this = Cache::getInstance();
		$settings = $_this->settings();
		if (empty($settings['engine']) || empty($_this->_Engine[$settings['engine']])) {
			return null;
		}

		if (!$key = $_this->_Engine[$settings['engine']]->key($key)) {
			return null;
		}
		$success = $_this->_Engine[$settings['engine']]->read($settings['prefix'] . $key);
		if ($success !== false) {
			return $success;
		}
		if (!$_this->_Engine[$settings['engine']]->__setKey($settings['prefix'] . $key)) {
			return null;
		}
		return false;
	}

/**
 * init method
 *
 * @param string $alias ''
 * @return void
 * @access protected
 */
	protected static function _init($alias = '') {
		if (!Configure::read()) {
			return ClassRegistry::init($alias);
		}
		$plugin = '';
		if (strpos($alias, '.')) {
			list($plugin, $alias) = explode('.', $alias);
			$plugin .= '.';
		}
		$table = Inflector::underscore(Inflector::pluralize($alias));
		$Inst = ClassRegistry::init(array(
			'alias' => $alias,
			'class' => $plugin . $alias,
			'table' => false
		));
		$db =& ConnectionManager::getDataSource($Inst->useDbConfig);
		$sources = $db->listSources();
		if (in_array($Inst->tablePrefix . $table, $sources)) {
			$Inst->setSource($table);
			return $Inst;
		}
		trigger_error("MiCache::_init Unable to set the source for the $plugin$alias model check that `$table` exists in the {$Inst->useDbConfig} datasource");
		return false;
	}
}
if (!class_exists('FileEngine')) {
	require LIBS . DS . 'cache' . DS . 'file.php';
}

/**
 * MiFileEngine class
 *
 * Allow subfolders in cache keys
 *
 * @uses          FileEngine
 * @package       mi
 * @subpackage    mi.branches.mi_plugin.vendors
 */
class MiFileEngine extends FileEngine {

/**
 * init method
 *
 * @param array $settings array()
 * @return void
 * @access public
 */
	function init($settings = array()) {
		parent::init(array_merge(
			array(
				'engine' => 'MiFile', 'path' => CACHE . 'data' . DS, 'prefix'=> '', 'lock'=> false,
				'serialize'=> true, 'isWindows' => false
			),
			$settings
		));
		if (!isset($this->__File)) {
			if (!class_exists('File')) {
				require LIBS . 'file.php';
			}
			$this->__File = new File($this->settings['path'] . DS . 'cake');
		}

		if (DIRECTORY_SEPARATOR === '\\') {
			$this->settings['isWindows'] = true;
		}

		$this->settings['path'] = $this->__File->Folder->cd($this->settings['path']);

		if (empty($this->settings['path'])) {
			return false;
		}
		return $this->__active();
	}

/**
 * generates a safe key - allow subfolders
 *
 * @param string $key the key passed over
 * @return mixed string $key or false
 * @access public
 */
	function key($key) {
		if (empty($key)) {
			return false;
		}
		$key = Inflector::underscore(str_replace(array('.'), '_', strval($key)));
		return $key;
	}

/**
 * Delete all the files, ignoring any dot files, and any file named 'empty'
 * For the not-windows version, also delete any empty folders when you're done
 *
 * @param boolean $check Optional - only delete expired cache items
 * @return boolean True if the cache was succesfully cleared, false otherwise
 * @access public
 */
	function clear($check = null) {
		if (!$this->__init) {
			return false;
		}

		$dir = dirname($this->settings['path']);

		if (DS !== '/') {
			$Folder = new Folder($dir);
			$files = $Folder->findRecursive('(?!\\.|empty).*');
			foreach($files as $file) {
				if (strpos($file, DS . '.')) {
					continue;
				}
				unlink($file);
			}
			return;
		}

		exec("find $dir -type f ! -iwholename \"*.svn*\" ! -name \"empty\" -exec rm -f {} \; && find $dir -type d -empty -print0 | xargs -0 rmdir", $_, $returnVar);
		if (!$returnVar) {
			return;
		}
	}

/**
 * destruct method
 *
 * @return void
 * @access private
 */
	function __destruct() {
		Cache::getInstance()->__name = 'default';
	}
}