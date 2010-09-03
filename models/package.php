<?php
class Package extends AppModel {
	var $name = 'Package';
	var $belongsTo = array('Maintainer');
	var $actsAs = array(
		'Searchable.Searchable' => array(
			'scope' => array('deleted' => 0),
			'summary' => 'description',
			'url' => array(
				'Package' => array('package' => 'name'),
				'Maintainer' => array('maintainer' => 'username')
			),
		),
		'Softdeletable'
	);
	var $folder = null;

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

	function __findAutocomplete($name = null) {
		if (!$name) return false;

		return $this->find('all', array(
			'cache' => true,
			'conditions' => array("{$this->alias}.{$this->displayField} LIKE" => "%{$name}%"),
			'contain' => false,
			'limit' => 10,
			'fields' => array($this->primaryKey, $this->displayField)
		));
	}

	function __findEdit($id = null) {
		if (!$id) return false;

		return $this->find('first', array(
			'conditions' => array("{$this->alias}.{$this->primaryKey}" => $id),
			'contain' => array('Maintainer')
		));
	}

	function __findIndex($params = array()) {
		$options = array_merge(array(
							'contain' => array('Maintainer'),
							'limit' => 10,
							'paginate' => true),
							$params['paginate']);

		if ($params['type']) $options['conditions'] = array("{$this->alias}.contains_{$params['type']}" => true);

		return $this->find('all', $options);
	}

	function __findLatest() {
		return $this->find('all', array(
			'cache' => 3600,
			'contain' => array('Maintainer' => array('username')),
			'fields' => array($this->displayField),
			'limit' => 5,
			'order' => "{$this->alias}.created DESC"
		));
	}

	function __findListForMaintainer($maintainer_id = null) {
		if (!$maintainer_id) return false;

		return $this->find('list', array(
			'conditions' => array("{$this->alias}.maintainer_id" => $maintainer_id),
			'order' => "{$this->alias}.{$this->displayField} DESC"
		));
	}

	function __findRandom() {
		$id = $this->find('random_ids', 5);

		return $this->find('all', array(
			'cache' => 600,
			'contain' => array('Maintainer' => array('username')),
			'fields' => array($this->displayField, 'maintainer_id'),
			'conditions' => array("{$this->alias}.{$this->primaryKey}" => $id)
		));
	}

	function __findRandomIds($limit = 5) {
		return $this->find('list', array(
			'cache' => true,
			'fields' => array($this->primaryKey),
			'order' => 'RAND()',
			'limit' => $limit
		));
	}

	function __findRepoClone($id = null) {
		if (!$id) return false;

		return $this->find('first', array(
			'conditions' => array("{$this->alias}.{$this->primaryKey}" => $id),
			'contain' => array('Maintainer'),
			'order' => array("{$this->alias}.{$this->primaryKey} ASC")
		));
	}

	function __findView($params = array()) {
		if (!isset($params['maintainer']) || !isset($params['package'])) return false;

		$maintainer_id = $this->Maintainer->find('maintainer_id', $params['maintainer']);

		if (!$maintainer_id) return false;

		return $this->find('first', array(
			'cache' => 3600,
			'conditions' => array(
				"{$this->alias}.{$this->displayField}" => $params['package'],
				"{$this->alias}.maintainer_id" => $maintainer_id),
			'contain' => array('Maintainer' => array($this->displayField, 'username')
		)));
	}



	function setupRepoDirectory($id = null) {
		if (!$id) return false;

		$package = $this->find('repo_clone', $id);
		if (!$package) return false;

		$tmp_dir = trim(TMP);
		$repo_dir = trim(TMP . 'repos');

		if (!$this->folder) $this->folder = new Folder();
		$this->folder->cd($tmp_dir);
		$existing_files_and_folders = $this->folder->read();
		if (!in_array('repos', $existing_files_and_folders['0'])) {
			$this->folder->create($repo_dir);
		}

		$repo_url = $package['Package']['repository_url'];
		$clone_path = strtolower($package['Maintainer']['username'][0]) . DS;
		$clone_path .= $package['Maintainer']['username'] . DS . $package['Package']['name'];
		shell_exec("cd {$repo_dir} ; git clone {$repo_url} {$clone_path}");
		return true;
	}

/**
 * Check's an individual repository of cakephp code
 * and updates it's attributes
 *
 * @param int $id primaryKey of a record
 * @return boolean true if update successful, false otherwise
 * @author Jose Diaz-Gonzalez
 */
	function classifyRepository($id) {
		if (!$id)  return false;

		$package = $this->find('repo_clone', $id);

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

	function afterSave($created = true) {
		if ($created) {
			$this->setupRepoDirectory($this->getLastInsertID());
			$this->classifyRepository($this->getLastInsertID());
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
