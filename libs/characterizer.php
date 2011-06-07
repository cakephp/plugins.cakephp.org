<?php
if (!class_exists('Folder')) {
	App::import('Core', 'Folder');
}

class Characterizer {

	protected $_Folder = null;

	protected $_has = array(
		'model'     => false,
		'view'      => false,
		'controller'=> false,
		'behavior'  => false,
		'helper'    => false,
		'shell'     => false,
		'datasource'=> false,
		'tests'     => false,
		'fixture'   => false,
		'themed'    => false,
		'elements'  => false,
		'vendor'    => false,
		'lib'       => false,
		'config'    => false,
		'resource'  => false,
		'plugin'    => false,
		'app'       => false,
	);

	protected $_path = null;

	protected $_classRegex = array(
		'model'     => array('/([\w]*)AppModel$/', '/^Model/'),
		'view'      => array('/([\w]*)View/'),
		'controller'=> array('/([\w]*)Controller$/', '/^Controller/'),
		'component' => array('/([\w]*)Component$/'),
		'behavior'  => array('/([\w]*)Behavior$/', '/^ModelBehavior/'),
		'helper'    => array('/([\w]*)Helper$/', '/^Helper/'),
		'shell'     => array('/([\w]*)Shell$/', '/([\w]*)Task$/', '/^Shell/'),
		'datasource'=> array('/([\w]*)Source$/'), // Need a better regex for this one
		'tests'     => array('/([\w]*)TestCase$/'),
		'fixture'   => array('/([\w]*)Fixture$/'),
		'themed'    => array(),
		'elements'  => array(),
		'vendor'    => array(),
		'lib'       => array(),
		'config'    => array(),
		'resource'  => array(),
		'plugin'    => array('/([\w]+)AppModel/', '/([\w]+)AppController/', '/([\w]+)AppHelper/', '/([\w]+)AppError/'),
		'app'       => array('/^AppModel/', '/^AppController/', '/^AppHelper/', '/^AppError/', '/^Model/', '/^Controller/', '/^Helper/'),
	);

	protected $_fileRegex = array(
		'model'     => array('/\/models\/([\w_]+).php/'),
		'view'      => array('/\/views\/([\w_]+).php/'),
		'controller'=> array('/\/controllers\/([\w_]+).php/'),
		'behavior'  => array('/\/behaviors\/([\w_]+).php/'),
		'helper'    => array('/\/helpers\/([\w_]+).php/'),
		'shell'     => array(),
		'datasource'=> array('/\/datasources\/([\w_]+).php/', '/\/dbo\/([\w_]+).php/'),
		'tests'     => array('/\/cases\/([\w_\/]+).php/'),
		'fixture'   => array('/\/fixtures\/([\w_]+).php/'),
		'themed'    => array('/\/themed\/([\w_\/]+).ctp/'),
		'elements'  => array('/\/elements\/([\w_\/]+).ctp/'),
		'vendor'    => array('/\/vendors\/([\w_]+).php/'), //
		'lib'       => array('/\/lib\/([\w_\/]+).php/'),
		'config'    => array('/\/config\/([\w_\/]+).php/'),
		'resource'  => array(), //
		'plugin'    => array(),
		'app'       => array('/\/app\//'),
	);

	public function __construct($path = null) {
		if (!$path) {
			throw new IllegalArgumentException("Invalid path passed");
		}

		if (!file_exists($path)) {
			throw new IllegalArgumentException("Invalid path passed");
		}

		$this->_path = $path;
		$this->_Folder = new Folder($this->_path);
	}

	public function classify() {
		$check = array();
		$classes = array();
		$files = $this->_Folder->tree($this->_path, true, 'file');

		foreach ($files as $filename) {
			if (!preg_match('/.php$/', $filename) && !preg_match('/.ctp$/', $filename)) {
				continue;
			}

			$check[] = str_replace($this->_path, '', $filename);
			$classes = array_merge($classes, $this->_getClasses($filename));
		}

		$classes = array_unique($classes);
		foreach ($classes as $class) {
			foreach ($this->_classRegex as $type => $regexes) {
				if (empty($regexes)) {
					continue;
				}

				foreach ($regexes as $regex) {
					if (preg_match($regex, $class)) {
						$this->_has[$type] = true;
					}
				}
			}
		}

		foreach ($check as $filename) {
			foreach ($this->_fileRegex as $type => $regexes) {
				if (empty($regexes)) {
					continue;
				}

				foreach ($regexes as $regex) {
					if (preg_match($regex, $filename)) {
						$this->_has[$type] = true;
					}
				}
			}
		}

		$data = array();
		foreach ($this->_has as $field => $value) {
			$data['contains_' . $field] = $value;
		}
		return $data;
	}

/**
 * Processes a filename for all inner classes
 *
 * @param string $filepath Full path to file
 * @return array Array of classes contained in a file
 */
	protected function _getClasses($filepath) {
		$contents = file_get_contents($filepath);
		$classes = array();
		$tokens = token_get_all($contents);

		$count = count($tokens);
		foreach ($tokens as $i => $token) {
			// Skip first two tokens by default
			if ($i < 2) {
				continue;
			}

			// Skip all tokens that cannot possibly be classes
			if ($token[0] !== T_STRING) {
				continue;
			}

			// It can be a class if it is preceeded by either
			// T_ABSTRACT, T_CLASS, T_INTERFACE, T_EXTENDS
			// Will skip classes that are preceeded by a comma and then
			// Any of the above tokens
			switch ($tokens[$i - 2][0]) {
				case T_ABSTRACT:
				case T_CLASS:
				case T_EXTENDS:
				case T_INTERFACE:
					$classes[] = $token[1];
					break;
			}
		}
		return $classes;
	}

}