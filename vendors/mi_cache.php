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
 * @copyright     Copyright (c) 2009, Andy Dawson
 * @link          www.ad7six.com
 * @package       mi
 * @subpackage    mi.vendors
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
 * @package       mi
 * @subpackage    mi.vendors
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
 * serialize - defaults to false, otherwise  a cached false is hard to identify
 * dirLevels - how many dir levels to create
 * dirLength - how many characters of the hask-key to be used for each folder name
 * probability - the garbage collection probability - the default is to effectively disable gc
 * batchLoadSettings - Enable batch loading of settings by default
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
		'serialize' => false,
		'dirLevels' => 2,
		'dirLength' => 1,
		'probability' => 98765432123456789,
		'batchLoadSettings' => true
	);

/**
 * appSettingCache property
 *
 * The per-request settings cache. The first time Some.specific.setting is requested the top level
 * key (Some) will be requested and stored here. This is likely to be faster than handling each
 * setting request individuallly (less cache engine activity)
 *
 * @var array
 * @access protected
 */
	static protected $_appSettingCache = array();

/**
 * Do we have a database?
 *
 * @var mixed null
 * @access protected
 */
	static protected $_hasDb = null;

/**
 * Do we have a settings table to refer to?
 *
 * If not put Configure::write('MiSettings.noDb', true) in your bootstrap so this class doesn't try and load the model
 *
 * @var mixed null
 * @access protected
 */
	static protected $_hasSettingsTable = null;

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
 * API DIFFERENCE
 * if a topKey is specified, delete everything under setting-dir/topKey
 *
 * @param bool $check false
 * @return void
 * @access public
 */
	public function clear($topKey = false) {
		if (MiCache::$setting === null) {
			MiCache::config();
		}
		if ($topKey) {
			unset(MiCache::$_appSettingCache['_' . $topKey]);
		} else {
			MiCache::$_appSettingCache = array();
		}
		Cache::clear($topKey, MiCache::$setting);
	}

/**
 * delete method
 *
 * Call with the same params as you would for data - deletes the cached result
 *
 * @return void
 * @access public
 */
	public function delete() {
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
	public function mi($name, $func = null) {
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
		$return = Cache::read($cacheKey, MiCache::$setting);
		if ($return) {
			return unserialize($return);
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
	public function data($name, $func) {
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
		$return = MiCache::read($cacheKey, MiCache::$setting, false);
		if ($return) {
			return unserialize($return);
		}

		if (!MiCache::_hasDb()) {
			return null;
		}

		if ($func === 'find') {
			$params[1]['miCache'] = 'cacheRequest';
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

		$offset = $prefix = null;
		if (is_string($string)) {
			$prefix = $string . DS;
		}

		if (count(func_get_args() > 1 || !is_string($string))) {
			if (!$prefix && is_string($string[0])) {
				$prefix = $string[0] . DS;
			}
			$string = serialize(func_get_args());
		}
		$hash = md5(Configure::read('Config.language') . $string);
		$config = current(MiCache::config());
		for ($i = 1; $i <= $config['dirLevels']; $i++) {
			$prefix .= substr($hash, $offset, $config['dirLength']) . DS;
			$offset = $i * $config['dirLength'];
		}
		return str_replace('.', '_', strtolower($prefix . $hash));
	}

/**
 * read method - directly query the cache
 *
 * @param mixed $cacheKey
 * @param mixed $setting null
 * @param bool $unserialize true
 * @return void
 * @access public
 */
	public static function read($cacheKey, $setting = null, $unserialize = true) {
		if (MiCache::$setting === null) {
			MiCache::config();
		}

		if (!$setting) {
			$setting = MiCache::$setting;
		}
		$return = Cache::read($cacheKey, $setting);
		if ($return && $unserialize) {
			return unserialize($return);
		}
		return $return;
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
	public static function setting($id = '', $aroId = null) {
		if (MiCache::$setting === null) {
			MiCache::config();
		}

		if (MiCache::$settings[MiCache::$setting]['batchLoadSettings']) {
		  	if (strpos($id, '.')) {
				$keys = explode('.', $id);
				$mainId = array_shift($keys);
				if (!array_key_exists($aroId . '_' . $mainId, MiCache::$_appSettingCache)) {
					MiCache::$_appSettingCache[$aroId . '_' . $mainId] = MiCache::setting($mainId, $aroId);
				}
				$array = MiCache::$_appSettingCache[$aroId . '_' . $mainId];
				$j = count($keys);
				$return = null;
				if (is_array($array)) {
					foreach($keys as $i => $key) {
						if (!array_key_exists($key, $array)) {
							$array = null;
							break;
						}
						$array = $array[$key];
					}
					if ($i == $j - 1) {
						$return = $array;
					}
				}
				if ($return !== null) {
					return $return;
				}
			}
		} else {
		  	if (strpos($id, '.')) {
				$keys = explode('.', $id, 1);
				$mainId = array_shift($keys);
				if (array_key_exists($aroId . '_' . $mainId, MiCache::$_appSettingCache) &&
					array_key_exists($id, MiCache::$_appSettingCache[$aroId . '_' . $mainId])) {
					return MiCache::$_appSettingCache[$aroId . '_' . $mainId][$id];
				}
			}
		}

		if (MiCache::_hasSettingsTable()) {
			$return = MiCache::data('MiSettings.Setting', 'data', $id, $aroId);
			if ($return !== null) {
				return $return;
			}
		}

		$return = Configure::read($id);
		$cacheKey = MiCache::key(array('MiSettings.Setting', 'data', $id, $aroId));
		MiCache::write($cacheKey, $return, MiCache::$setting);
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

		if (!$setting || !isset(MiCache::$settings[$setting])) {
			$setting = MiCache::$setting;
		}
		$settings = MiCache::$settings[$setting];
		$path = dirname($settings['path'] . $settings['prefix'] . $cacheKey);

		if (MiCache::_createDir($path)) {
			return Cache::write($cacheKey, serialize($data), $setting);
		}
		return false;
	}

/**
 * exec method
 *
 * @param mixed $cmd
 * @param mixed $out null
 * @return void
 * @access protected
 */
	static protected function _exec($cmd, &$out = null) {
		if (!class_exists('Mi')) {
			App::import('Vendor', 'Mi.Mi');
		}
		return Mi::exec($cmd, $out);
	}

/**
 * createDir method
 *
 * If the dir doesn't exist - create it
 *
 * @param mixed $path
 * @return void
 * @access protected
 */
	static protected function _createDir($path) {
		if (!is_dir($path)) {
			new Folder($path, true);
		}
		return is_writable($path);
	}

/**
 * Have we got a database?
 *
 * @return void
 * @access protected
 */
	static protected function _hasDb() {
		if (MiCache::$_hasDb === null) {
			MiCache::$_hasDb = file_exists(CONFIGS . 'database.php');
		}
		if (MiCache::$_hasDb) {
			return true;
		}
		return false;
	}

/**
 * Do we have a settings table?
 *
 * If not put Configure::write('MiSettings.noDb', true) in your bootstrap
 *
 * @return void
 * @access protected
 */
	static protected function _hasSettingsTable() {
		if (MiCache::$_hasSettingsTable === null) {
			MiCache::$_hasSettingsTable = !Configure::read('MiSettings.noDb');
		}
		return MiCache::$_hasSettingsTable;
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
 * @subpackage    mi.vendors
 */
class MiFileEngine extends FileEngine {

/**
 * init method
 *
 * Note the serialize param refers to the underlying cache engine. MiCache is always
 * storing serialized strings
 *
 * @param array $settings array()
 * @return void
 * @access public
 */
	public function init($settings = array()) {
		parent::init(array_merge(array(
			'engine' => 'MiFile',
			'path' => CACHE . 'data' . DS,
			'prefix'=> '',
			'lock'=> false,
			'serialize'=> false,
			'isWindows' => false
		), $settings));
		if (!isset($this->_File)) {
			if (!class_exists('File')) {
				require LIBS . 'file.php';
			}
			$this->_File = new File($this->settings['path'] . DS . 'cake');
		}

		if (DIRECTORY_SEPARATOR === '\\') {
			$this->settings['isWindows'] = true;
		}

		$this->settings['path'] = $this->_File->Folder->cd($this->settings['path']);

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
	public function key($key) {
		if (empty($key)) {
			return false;
		}
		$key = Inflector::underscore(str_replace(array('.'), '_', strval($key)));
		return $key;
	}

/**
 * Delete everything under the requested cache setting.
 *
 * API DIFFERENCE
 * if a topKey is specified, delete everything under setting-dir/topKey
 *
 * @param mixed $topKey Optional - the top level cache key to delete
 * @return boolean True if the cache was succesfully cleared, false otherwise
 * @access public
 */
	public function clear($topKey = null) {
		if (empty($this->_init)) {
			return false;
		}

		$dir = $this->settings['path'];

		if ($topKey && $topKey !== true) {
			$dir .= DS . str_replace('.', '_', strtolower($topKey));
		}

		if (DS === '\\') {
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
		return MiFileEngine::_exec("rm -rf $dir/*");
	}

/**
 * exec method
 *
 * @param mixed $cmd
 * @param mixed $out null
 * @return void
 * @access protected
 */
	static protected function _exec($cmd, &$out = null) {
		if (!class_exists('Mi')) {
			App::import('Vendor', 'Mi.Mi');
		}
		return Mi::exec($cmd, $out);
	}

/**
 * destruct method
 *
 * Prevent potential cache Confusion
 *
 * @TODO still necessary?
 * @return void
 * @access private
 */
	public function __destruct() {
		Cache::getInstance()->__name = 'default';
	}
}