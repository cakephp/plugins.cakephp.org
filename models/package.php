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
			'contain' => array('Maintainer' => array('fields' => array('id', 'username'))),
			'fields' => array($this->primaryKey, $this->displayField, 'repository_url'),
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



	function _setupRepoDirectory($id = null) {
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

	function afterSave($created = true) {
		if ($created) {
			$this->_setupRepoDirectory($this->getLastInsertID());
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
		return $this->Package->save($package);
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
?>