<?php
class Package extends AppModel {
	var $name = 'Package';
	var $belongsTo = array('Maintainer');
	var $actsAs = array(
		'Searchable.Searchable' => array(
			'scope' => array('deleted' => 0),
			'summary' => 'description',
			'allowNumericKeys' => true,
			'url' => array(
				'Package' => array(1 => 'name'),
				'Maintainer' => array(0 => 'username')
			),
		),
		'Softdeletable'
	);
	var $validTypes = array(
		'model', 'controller', 'view',
		'behavior', 'component', 'helper',
		'shell', 'theme', 'datasource',
		'lib', 'test', 'vendor',
		'app', 'config', 'resource',
	);
	var $folder = null;
	var $_findMethods = array(
		'autocomplete'      => true,
		'edit'              => true,
		'index'             => true,
		'latest'            => true,
		'listformaintainer' => true,
		'random'            => true,
		'randomids'         => true,
		'repoclone'         => true,
		'view'              => true,
	);

	function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->order = "`{$this->alias}`.`{$this->displayField}` asc";
		$this->validate = array(
			'maintainer_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => __('must contain only numbers', true),
				),
			),
			'name' => array(
				'notempty' => array(
					'rule' => array('notempty'),
					'message' => __('cannot be left empty', true),
				),
			),
		);
	}

	function _findAutocomplete($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Invalid query', true));
			}

			$query['cache'] = true;
			$query['conditions'] = array("{$this->alias}.{$this->displayField} LIKE" => "%{$query[0]}%");
			$query['contain'] = false;
			$query['fields'] = array($this->primaryKey, $this->displayField);
			$query['limit'] = 10;
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	function _findEdit($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Invalid package', true));
			}

			$query['contain'] = array('Maintainer');
			$query['conditions'] = array("{$this->alias}.{$this->primaryKey}" => $query[0]);
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new OutOfBoundsException(__('Invalid package', true));
			}
			return $results[0];
		}
	}

	function _findIndex($state, $query, $results = array()) {
		if ($state == 'before') {
			if (!empty($query['paginate_type']) && in_array($query['paginate_type'], $this->validTypes)) {
				$query['conditions'] = array("{$this->alias}.contains_{$query['paginate_type']}" => true);
			}

			$query['contain'] = array('Maintainer' => array('id','username', 'name'));
			$query['fields'] = array_diff(
				array_keys($this->schema()),
				array('deleted', 'created', 'modified', 'repository_url', 'homepage', 'tags', 'bakery_article')
			);
			if (!empty($query['operation'])) {
				return $this->_findCount($state, $query, $results);
			}
			return $query;
		} elseif ($state == 'after') {
			if (!empty($query['operation'])) {
				return $this->_findCount($state, $query, $results);
			}
			return $results;
		}
	}

	function _findLatest($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['contain'] = array('Maintainer' => array('id','username', 'name'));
			$query['fields'] = array_diff(
				array_keys($this->schema()),
				array('deleted', 'created', 'modified', 'repository_url', 'homepage', 'tags', 'bakery_article')
			);
			$query['limit'] = (empty($query['limit'])) ? 5 : $query['limit'];
			$query['order'] = array("{$this->alias}.created DESC");
			if (!empty($query['operation'])) {
				return $this->_findCount($state, $query, $results);
			}
			return $query;
		} elseif ($state == 'after') {
			if (!empty($query['operation'])) {
				return $this->_findCount($state, $query, $results);
			}
			return $results;
		}
	}

	function _findListformaintainer($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Invalid package', true));
			}

			$query['conditions'] = array("{$this->alias}.maintainer_id" => $query[0]);
			$query['fields'] = array("{$this->alias}.{$this->primaryKey}", "{$this->alias}.{$this->displayField}");
			$query['order'] = array("{$this->alias}.{$this->displayField} DESC");
			$query['recursive'] = -1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results)) {
				return array();
			}
			return Set::combine(
				$results,
				"{n}.{$this->alias}.{$this->primaryKey}",
				"{n}.{$this->alias}.{$this->displayField}"
			);
		}
	}

	function _findRandom($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['cache'] = 600;
			$query['conditions'] = array("{$this->alias}.{$this->primaryKey}" => $this->find('randomids'));
			$query['contain'] = array('Maintainer' => array('username'));
			$query['fields'] = array("{$this->alias}.$this->displayField", "{$this->alias}.maintainer_id");
			return $query;
		} elseif ($state == 'after') {
			return $results;
		}
	}

	function _findRandomids($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['fields'] = array("{$this->alias}.{$this->primaryKey}", "{$this->alias}.{$this->primaryKey}");
			$query['group'] = array("{$this->alias}.maintainer_id");
			$query['limit'] = (empty($query[0])) ? 5 : $query[0];
			$query['order'] = array('RAND()');
			$query['recursive'] = -1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results)) {
				return array();
			}
			return Set::combine(
				$results,
				"{n}.{$this->alias}.{$this->primaryKey}",
				"{n}.{$this->alias}.{$this->primaryKey}"
			);
		}
	}

	function _findRepoclone($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Invalid package', true));
			}

			$query['conditions'] = array("{$this->alias}.{$this->primaryKey}" => $query[0]);
			$query['contain'] = array('Maintainer');
			$query['limit'] = 1;
			$query['order'] = array("{$this->alias}.{$this->primaryKey} ASC");
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new OutOfBoundsException(__('Invalid package', true));
			}
			return $results[0];
		}
	}

	function _findView($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query['maintainer']) || empty($query['package'])) {
				throw new InvalidArgumentException(__('Invalid package', true));
			}

			$query['cache'] = 3600;
			$query['conditions'] = array(
				"{$this->alias}.{$this->displayField}" => $query['package'],
				'Maintainer.username' => $query['maintainer'],
			);
			$query['contain'] = array('Maintainer' => array($this->displayField, 'username'));
			$query['limit'] = 1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				throw new OutOfBoundsException(__('Invalid package', true));
			}
			return $results[0];
		}
	}

	function setupRepoDirectory($id = null) {
		if (!$id) return false;

		$package = $this->find('repoclone', $id);
		if (!$package) return false;

		if (!$this->folder) $this->folder = new Folder();

		$path = rtrim(trim(TMP), DS);
		$appends = array(
			'repos',
			strtolower($package['Maintainer']['username'][0]),
			$package['Maintainer']['username'],
		);

		foreach ($appends as $append) {
			$this->folder->cd($path);
			$read = $this->folder->read();

			if (!in_array($append, $read['0'])) {
				$this->folder->create($path . DS . $append);
			}
			$path = $path . DS . $append;
		}

		$this->folder->cd($path);
		$read = $this->folder->read();

		if (!in_array($package['Package']['name'], $read['0'])) {
			if (($paths = Configure::read('paths')) !== false) {
				putenv('PATH=' . implode(':', $paths) . ':' . getenv('PATH'));
			}
			$var = shell_exec(sprintf("cd %s ; git clone %s %s%s%s 2>&1 1> /dev/null",
				$path,
				$package['Package']['repository_url'],
				$path,
				DS,
				$package['Package']['name']
			));
			if (stristr($var, 'fatal')) return false;
		}
		return $package;
	}

/**
 * Check's an individual repository of cakephp code
 * and updates it's attributes
 *
 * @param int $id primaryKey of a record
 * @return boolean true if update successful, false otherwise
 * @author Jose Diaz-Gonzalez
 */
	function classifyRepository($package) {
		$repo_dir = trim(TMP . 'repos');
		$letter = $package['Maintainer']['username'][0];
		$username = $package['Maintainer']['username'];
		$repository = $package['Package']['name'];
		$characteristics = $this->__getCharacteristics(
			$repo_dir . DS . strtolower($letter) . DS . $username . DS . $repository
		);

		foreach ($characteristics as $characteristic) {
			$package['Package'][$characteristic] = 1;
		}
		if (!$this->save($package)) return false;
		return $characteristics;
	}

/**
 * Begins classification of a repository by checking for
 * the existence of an 'app' folder and adjusting accordingly
 * before classifying it's contents
 *
 * @param string $repository_path path to a git repository on disk
 * @return array an array of characteristics
 * @access protected
 * @package default
 * @author Jose Diaz-Gonzalez
 */
	function __getCharacteristics($repository_path = null) {
		if (!$repository_path) return false;

		$characteristics = array();
		if (!$this->folder) $this->folder = new Folder();
		$this->folder->cd($repository_path);
		$contents = $this->folder->read();

		if (in_array('app', $contents[0])) {
			$characteristics[] = 'contains_app';
			$this->folder->cd($repository_path . DS . 'app');
			$contents = $this->folder->read();
		}
		$characteristics = array_merge($this->__classifyContents($repository_path, $contents), $characteristics);

		return $characteristics;
	}

/**
 * Classifies the contents of a repository based upon raw
 * Folder::cd() and Folder::read() methods
 *
 * @param string $repository_path path to a git repository on disk
 * @param array an array of files and folders in the base repository path
 * @return array an array of characteristics
 * @access protected
 * @package default
 * @author Jose Diaz-Gonzalez
 */
	function __classifyContents($repository_path, $contents = array()) {
		$characteristics = array();
		$resources = null;
		if (in_array('models', $contents[0])) {
			// We might have some Models
			$this->folder->cd($repository_path . DS . 'models');
			$model_contents = $this->folder->read();
			if (!empty($model_contents[1]) && (count($model_contents[1]) != 1 || $model_contents[1][0] != 'empty')) {
				$characteristics[] = 'contains_model';
			}
			if (in_array('datasources', $model_contents[0])) {
				$this->folder->cd($repository_path . DS . 'models' . DS . 'datasources');
				$datasource_contents = $this->folder->read();
				if (in_array('dbo', $datasource_contents[0])) {
					$this->folder->cd($repository_path . DS . 'models' . DS . 'datasources' . DS . 'dbo');
					$dbo_contents = $this->folder->read();
					if (!empty($dbo_contents[1]) && (count($dbo_contents[1]) != 1 || $dbo_contents[1][0] != 'empty')) {
						$characteristics[] = 'contains_datasource';
					}
				}
				if (!empty($datasource_contents[1]) && !in_array('contains_datasource', $characteristics)) {
					if (count($datasource_contents[1]) != 1 || $datasource_contents[1][0] != 'empty') {
						$characteristics[] = 'contains_datasource';
					}
				}
			}
			if (in_array('behaviors', $model_contents[0])) {
				$this->folder->cd($repository_path . DS . 'models' . DS . 'behaviors');
				$behavior_contents = $this->folder->read();
				if (!empty($behavior_contents[1]) && (count($behavior_contents[1]) != 1 || $behavior_contents[1][0] != 'empty')) {
					$characteristics[] = 'contains_behavior';
				}
			}
		}
		if (in_array('controllers', $contents[0])) {
			$this->folder->cd($repository_path . DS . 'controllers');
			$controller_contents = $this->folder->read();
			if (!empty($controller_contents[1]) && (count($controller_contents[1]) != 1 || $controller_contents[1][0] != 'empty')) {
				$characteristics[] = 'contains_controller';
			}
			if (in_array('components', $controller_contents[0])) {
				$this->folder->cd($repository_path . DS . 'controllers' . DS . 'components');
				$component_contents = $this->folder->read();
				if (!empty($component_contents[1]) && (count($component_contents[1]) != 1 || $component_contents[1][0] != 'empty')) {
					$characteristics[] = 'contains_component';
				}
			}
		}
		if (in_array('views', $contents[0])) {
			$this->folder->cd($repository_path . DS . 'views');
			$view_contents = $this->folder->read();
			if (in_array('helpers', $view_contents[0])) {
				$view_contents[0] = array_diff($view_contents[0], array('helpers'));
				$this->folder->cd($repository_path . DS . 'views' . DS . 'helpers');
				$helper_contents = $this->folder->read();
				if (!empty($helper_contents[1]) && (count($helper_contents[1]) != 1 || $helper_contents[1][0] != 'empty')) {
					$characteristics[] = 'contains_helper';
				}
			}
			if (in_array('themed', $view_contents[0])) {
				$this->folder->cd($repository_path . DS . 'views' . DS . 'themed');
				$theme_contents = $this->folder->read();
				if (!empty($theme_contents[0])) {
					$characteristics[] = 'contains_theme';
				}
				$view_contents[0] = array_diff($view_contents[0], array('themed'));
			}
			if (in_array('elements', $view_contents[0])) {
				$view_contents[0] = array_diff($view_contents[0], array('elements'));
			}

			if (!empty($view_contents[0])) {
				$characteristics[] = 'contains_view';
			}
		}
		if (in_array('vendors', $contents[0])) {
			$this->folder->cd($repository_path . DS . 'vendors');
			$vendor_contents = $this->folder->read();
			$vendor_contents[1] = array_diff($vendor_contents[1], array('empty'));
			if (in_array('shells', $vendor_contents[0])) {
				$this->folder->cd($repository_path . DS . 'vendors' . DS . 'shells');
				$shell_contents = $this->folder->read();
				if (!empty($shell_contents[1]) && (count($shell_contents[1]) != 1 || $shell_contents[1][0] != 'empty')) {
					$characteristics[] = 'contains_shell';
				}
				$vendor_contents[0] = array_diff($vendor_contents[0], array('shells'));
			}
			if (in_array('css', $vendor_contents[0])) {
				$this->folder->cd($repository_path . DS . 'vendors' . DS . 'css');
				$resource_contents = $this->folder->read();
				if (!empty($resource_contents[1]) && (count($resource_contents[1]) != 1 || $resource_contents[1][0] != 'empty')) {
					$resources = true;
				}
				$vendor_contents[0] = array_diff($vendor_contents[0], array('css'));
			}
			if (in_array('js', $vendor_contents[0])) {
				$this->folder->cd($repository_path . DS . 'vendors' . DS . 'js');
				$resource_contents = $this->folder->read();
				if (!empty($resource_contents[1]) && (count($resource_contents[1]) != 1 || $resource_contents[1][0] != 'empty')) {
					$resources = true;
				}
				$vendor_contents[0] = array_diff($vendor_contents[0], array('js'));
			}
			if (in_array('img', $vendor_contents[0])) {
				$this->folder->cd($repository_path . DS . 'vendors' . DS . 'img');
				$resource_contents = $this->folder->read();
				if (!empty($resource_contents[1]) && (count($resource_contents[1]) != 1 || $resource_contents[1][0] != 'empty')) {
					$resources = true;
				}
				$vendor_contents[0] = array_diff($vendor_contents[0], array('img'));
			}
			if (!empty($vendor_contents[0]) || !empty($vendor_contents[1])) {
				$characteristics[] = 'contains_vendor';
			}
		}
		if (in_array('tests', $contents[0])) {
			$this->folder->cd($repository_path . DS . 'tests');
			$test_contents = $this->folder->read();
			if (!empty($test_contents[1]) && (count($test_contents[1]) != 1 || $test_contents[1][0] != 'empty')) {
				$characteristics[] = 'contains_test';
			}
		}
		if (in_array('libs', $contents[0])) {
			$this->folder->cd($repository_path . DS . 'libs');
			$lib_contents = $this->folder->read();
			if (!empty($lib_contents[1]) && (count($lib_contents[1]) != 1 || $lib_contents[1][0] != 'empty')) {
				$characteristics[] = 'contains_lib';
			}
		}
		if (in_array('config', $contents[0])) {
			$this->folder->cd($repository_path . DS . 'config');
			$config_contents = $this->folder->read();
			if (!empty($config_contents[1]) && (count($config_contents[1]) != 1 || $config_contents[1][0] != 'empty')) {
				$characteristics[] = 'contains_config';
			}
		}
		if (in_array('webroot', $contents[0])) {
			$this->folder->cd($repository_path . DS . 'webroot');
			$webroot_contents = $this->folder->read();
			if (in_array('css', $webroot_contents[0])) {
				$this->folder->cd($repository_path . DS . 'webroot' . DS . 'css');
				$resource_contents = $this->folder->read();
				if (!empty($resource_contents[1]) && (count($resource_contents[1]) != 1 || $resource_contents[1][0] != 'empty')) {
					$resources = true;
				}
				$webroot_contents[0] = array_diff($webroot_contents[0], array('css'));
			}
			if (in_array('js', $webroot_contents[0])) {
				$this->folder->cd($repository_path . DS . 'webroot' . DS . 'js');
				$resource_contents = $this->folder->read();
				if (!empty($resource_contents[1]) && (count($resource_contents[1]) != 1 || $resource_contents[1][0] != 'empty')) {
					$resources = true;
				}
				$webroot_contents[0] = array_diff($webroot_contents[0], array('js'));
			}
			if (in_array('img', $webroot_contents[0])) {
				$this->folder->cd($repository_path . DS . 'webroot' . DS . 'img');
				$resource_contents = $this->folder->read();
				if (!empty($resource_contents[1]) && (count($resource_contents[1]) != 1 || $resource_contents[1][0] != 'empty')) {
					$resources = true;
				}
				$webroot_contents[0] = array_diff($webroot_contents[0], array('img'));
			}
		}
		if ($resources) $characteristics[] = 'contains_resources';
		return $characteristics;
	}

	function afterSave($created) {
		if ($created === true) {
			$id = $this->getLastInsertID();
			$package = $this->setupRepoDirectory($id);
			if ($package) {
				$this->classifyRepository($package);
			}
		}
	}

	function getSearchableData($data) {
		$searchableData = array();
		foreach ($data as $modelName => $modelData) {
			foreach ($modelData as $field => $value) {
				$searchableData["{$modelName}.{$field}"] = $value;
			}
		}
		return $searchableData;
	}

	function getAllSearchableData() {
		return $this->find('all', array(
			'conditions' => array('deleted' => 0),
			'contain' => array('Maintainer' => array(
				'fields' => array('name', 'username', 'twitter_username')
			))
		));
	}

	function updateAttributes($package, $attrs = array()) {
		if (empty($attrs) || !isset($attrs['Repository'])) return false;

		if (!empty($repo['Repository']['homepage'])) {
			if (is_array($repo['Repository']['homepage'])) {
				$package['Package']['homepage'] = $repo['Repository']['homepage']['value'];
			} else {
				$package['Package']['homepage'] = $repo['Repository']['homepage'];
			}
		} else if (!empty($repo['Repository']['url'])) {
			$package['Package']['homepage'] = $repo['Repository']['url'];
		}

		if (isset($attrs['Repository']['description'])) {
			$package['Package']['description'] = $attrs['Repository']['description'];
		}
		$this->create();
		return $this->save($package);
	}

	function fixRepositoryUrl($package = null) {
		if (!$package) return false;

		if (!is_array($package)) {
			$package = $this->find('first', array(
				'conditions' => array("{$this->alias}.{$this->primaryKey}" => $package),
				'contain' => array('Maintainer' => array('fields' => 'username')),
				'fields' => array('name', 'repository_url')
			));
		}
		if (!$package) return false;

		$package[$this->alias]['repository_url']	= array();
		$package[$this->alias]['repository_url'][]	= "git://github.com";
		$package[$this->alias]['repository_url'][]	= $package['Maintainer']['username'];
		$package[$this->alias]['repository_url'][]	= $package[$this->alias]['name'];
		$package[$this->alias]['repository_url']	= implode("/", $package[$this->alias]['repository_url']);
		$package[$this->alias]['repository_url']   .= '.git';
		return $this->save($package);
	}

	function checkExistenceOf($package = null) {
		if (!$package) return false;

		if (!is_array($package)) {
			$package = $this->find('first', array(
				'conditions' => array("{$this->alias}.{$this->primaryKey}" => $package),
				'contain' => array('Maintainer' => array('fields' => 'username')),
				'fields' => array('name', 'repository_url')
			));
		}
		if (!$package) return false;

		$response = ClassRegistry::init('Github')->find('repos_show_single', array(
			'username' => $package['Maintainer']['username'],
			'repo' => $package[$this->alias]['name']
		));

		if (!empty($response['Error'])) return false;
		return true;
	}

}