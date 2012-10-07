<?php
App::uses('Characterizer', 'Lib');
App::uses('DebugTimer', 'DebugKit.Lib');
App::uses('HttpSocket', 'Network/Http');
App::uses('Sanitize', 'Utility');
App::uses('Xml', 'Utility');
App::uses('Folder', 'Utility');

/**
 * Package Model
 */
class Package extends AppModel {

	public $name = 'Package';

	public $actsAs = array(
		'Favorites.Favorite',
		'Ratings.Ratable' => array(
			'calculation' => 'sum',
			'modelClass' => 'Package',
			'update' => true,
		),
		'Softdeletable',
		'CakePackagesTaggable',
	);

	public $belongsTo = array(
		'Category' => array(
			'className' => 'Categories.Category',
			'foreignKey' => 'category_id'
		),
		'Maintainer'
	);

	public $findMethods = array(
		'bookmark'          => true,
		'category'          => true,
		'download'          => true,
		'index'             => true,
		'listformaintainer' => true,
		'rate'              => true,
		'repoclone'         => true,
		'uncategorized'     => true,
		'view'              => true,
	);

	static $_allowedFilters = array(
		'collaborators', 'contains', 'contributors',
		'direction', 'forks', 'has', 'open_issues',
		'query', 'sort', 'since', 'watchers', 'with',
		'category'
	);

	public $_categories = array(
		'Admin Interface',
		'Anti-spam',
		'API Creation',
		'Application',
		'Asset Handling',
		'Authentication',
		'Authorization',
		'Background Processing',
		'Caching',
		'Categorization',
		'Configuration',
		'Controller',
		'Deployment',
		'Developer Tools',
		'Datasources',
		'Example',
		'Email',
		'Error Handling',
		'File Managers/Uploading',
		'Forms',
		'Internationalization',
		'Maps',
		'Messaging',
		'Model',
		'Navigation',
		'Other/Unknown',
		'Payment Processing',
		'Plugin Application',
		'Personal',
		'Reporting',
		'Routing',
		'Search',
		'Security',
		'SEO',
		'Skeleton',
		'Social',
		'Testing',
		'Third-party Apis',
		'User Management',
		'Utility',
		'View',
		'WYSIWYG editors',
	);

	static $_validOrders = array(
		'collaborators', 'contributors',
		'created', 'forks', 'last_pushed_at',
		'open_issues', 'watchers'
	);

	static $_validShownOrders = array(
		'created' => 'Created',
		'forks' => 'Forks',
		'last_pushed_at' => 'Last Pushed',
		'watchers' => 'Watchers'
	);

/**
 * Valid types of `contains_` or `has`
 *
 * @var array
 */
	public $_validTypes = array(
		'model', 'controller', 'view',
		'behavior', 'component', 'helper',
		'shell', 'theme', 'datasource',
		'lib', 'test', 'vendor',
		'app', 'config', 'resource',
	);

	public $_Folder = null;

	public $_Github = null;

	public $_HttpSocket = null;

	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->validate = array(
			'maintainer_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => __('must contain only numbers'),
				),
			),
			'name' => array(
				'notempty' => array(
					'rule' => array('notempty'),
					'message' => __('cannot be left empty'),
				),
			),
		);
		$this->tabs = array(
			'ratings'    => array('text' => __('Rating'),       'sort' => 'rating'),
			'watchers'   => array('text' => __('Watchers'),     'sort' => 'watchers', 'direction' => 'desc'),
			'title'      => array('text' => __('Title'),        'sort' => 'name'),
			'maintainer' => array('text' => __('Maintainer'),   'sort' => 'Maintainer.name'),
			'date'       => array('text' => __('Date Created'), 'sort' => 'created_at'),
			'updated'    => array('text' => __('Date Updated'), 'sort' => 'last_pushed_at'),
		);
	}

/**
 * Find a ratable package
 *
 * @param string $state
 * @param array $query
 * @param array $results
 * @return array
 * @todo Require that the user not own the package being rated
 */
	public function _findBookmark($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[$this->primaryKey])) {
				throw new NotFoundException(__("Cannot like a non-existent package"));
			}
			if (empty($query['user_id'])) {
				throw new UnauthorizedException(__("You must be logged in in order to rate packages"));
			}

			$query['conditions'] = array(
				"{$this->alias}.{$this->primaryKey}" => $query[$this->primaryKey],
			);
			$query['limit'] = 1;

			$query['fields'] = array($this->primaryKey);
			if (!empty($query['fields'])) {
				$query['fields'] = array_merge(
					$this->getDataSource()->fields($this, null, $query['fields']),
					$this->Rating->getDataSource()->fields($this->Favorite)
				);
			} else {
				$query['fields'] = array_merge(
					$this->getDataSource()->fields($this),
					$this->Rating->getDataSource()->fields($this->Favorite)
				);
			}

			$this->unbindModel(array(
				'hasMany' => array('Favorites.Favorite'),
			));
			$query['joins'] = array(
				array(
					'alias' => 'Favorite',
					'table' => 'favorites',
					'type' => 'LEFT',
					'conditions' => array(
						'Favorite.model' => 'Package',
						'Favorite.type' => 'bookmark',
						'Favorite.foreign_key' => $query['id'],
						'Favorite.user_id' => $query['user_id'],
					),
				),
			);
			return $query;
		}

		if (empty($results[0])) {
			throw new NotFoundException(__("Cannot like a non-existent package"));
		}

		if (empty($results[0]['Favorite']['id'])) {
			$results[0]['Favorite'] = false;
		}

		return $results[0];
	}

	public function _findCategory($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['conditions'] = array(
				"{$this->alias}.{$this->primaryKey}" => $query[$this->primaryKey],
			);
			$query['fields'] = array($this->primaryKey, 'category_id', 'name', 'description');
			$query['limit'] = 1;
			return $query;
		}

		if (empty($results[0])) {
			throw new NotFoundException(__('Invalid package'));
		}
		return $results[0];
	}

	public function _findDownload($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['conditions'] = array(
				"{$this->alias}.{$this->primaryKey}" => $query[$this->primaryKey],
			);
			$query['contain'] = array('Maintainer' => array('username'));
			$query['fields'] = array($this->primaryKey, 'name');
			$query['limit'] = 1;
			return $query;
		}

		if (empty($results[0])) {
			return false;
		}
		return sprintf(
			'https://github.com/%s/%s/zipball/%s',
			$results[0]['Maintainer']['username'],
			$results[0]['Package']['name'],
			$query['branch']
		);
	}

	public function _findIndex($state, $query, $results = array()) {
		if ($state == 'before') {
			if (!isset($query['named'])) {
				$query['named'] = array();
			}
			$query['named'] = array_merge(array(
				'collaborators' => null,
				'contains'      => array(),
				'contributors'  => null,
				'forks'         => null,
				'has'           => array(),
				'open_issues'   => null,
				'query'         => null,
				'since'         => null,
				'watchers'      => null,
				'with'          => array(),
			), $query['named']);

			$query['named']['has'] = array_merge(
				(array) $query['named']['with'],
				(array) $query['named']['contains'],
				(array) $query['named']['has']
			);

			$query['conditions'] = array("{$this->alias}.deleted" => false);
			$query['contain'] = array('Maintainer' => array('id', 'username', 'name'));
			$query['fields'] = array(
				$this->primaryKey, 'maintainer_id', 'name', 'description',
				'open_issues', 'forks', 'watchers', 'collaborators', 'contributors',
				'created_at', 'last_pushed_at', 'created'
			);

			$direction = 'desc';
			if (!empty($query['named']['direction'])) {
				$query['named']['direction'] = strtolower((string) $query['named']['direction']);
				if ($query['named']['direction'] == 'dsc' || $query['named']['direction'] == 'des') {
					$query['named']['direction'] = 'desc';
				}

				if ($query['named']['direction'] != 'asc' && $query['named']['direction'] != 'desc') {
					$query['named']['direction'] = 'desc';
				}
				$direction = $query['named']['direction'];
			}

			$sortField = 'created';
			if (!empty($query['named']['sort'])) {
				$query['named']['sort'] = strtolower($query['named']['sort']);
				if (in_array($query['named']['sort'], Package::$_validOrders)) {
					$sortField = $query['named']['sort'];
				}
			}

			$query['order'] = array(array("{$this->alias}.{$sortField} {$direction}"));

			if ($query['named']['collaborators'] !== null) {
				$query['conditions']["{$this->alias}.collaborators >="] = (int) $query['named']['collaborators'];
			}

			if ($query['named']['contributors'] !== null) {
				$query['conditions']["{$this->alias}.contributors >="] = (int) $query['named']['contributors'];
			}

			if ($query['named']['forks'] !== null) {
				$query['conditions']["{$this->alias}.forks >="] = (int) $query['named']['forks'];
			}

			if (!empty($query['named']['has'])) {
				foreach ($query['named']['has'] as $has) {
					$has = inflector::singularize(strtolower($has));
					if (in_array($has, $this->_validTypes)) {
						$query['conditions'][] = array(
							'Tag.keyname' => $has,
							'Tag.identifier' => 'contains',
						);
					}
				}
				$query['joins'][] = array(
					'alias' => 'Tagged',
					'table' => 'tagged',
					'type' => 'INNER',
					'conditions' => array(
						'`Tagged`.`foreign_key` = `' . $this->alias . '`.`id`',
					),
				);
				$query['joins'][] = array(
					'alias' => 'Tag',
					'table' => 'tags',
					'type' => 'INNER',
					'conditions' => array(
						'`Tagged`.`tag_id` = `Tag`.`id`',
					),
				);
			}

			if (!empty($query['named']['category'])) {
				$this->unbindModel(array(
					'belongsTo' => array('Categories.Category'),
				));
				$query['joins'][] = array(
					'alias' => 'Category',
					'table' => 'categories',
					'type' => 'INNER',
					'conditions' => array(
						'`Category`.`id` = `Package`.`category_id`',
						'`Category`.`slug`' => $query['named']['category'],
					),
				);
			}

			if ($query['named']['open_issues'] !== null) {
				$query['conditions']["{$this->alias}.open_issues <="] = (int) $query['named']['open_issues'];
			}

			if ($query['named']['query'] !== null) {
				$query['conditions'][]['OR'] = array(
					"{$this->alias}.name LIKE" => '%' . $query['named']['query'] . '%',
					"{$this->alias}.description LIKE" => '%' . $query['named']['query'] . '%',
					"Maintainer.username LIKE" => '%' . $query['named']['query'] . '%',
				);
			}

			if ($query['named']['since'] !== null) {
				$time = date('Y-m-d H:i:s', strtotime($query['named']['since']));
				$query['conditions']["{$this->alias}.last_pushed_at >"] = $time;
			}

			if ($query['named']['watchers'] !== null) {
				$query['conditions']["{$this->alias}.watchers >="] = (int) $query['named']['watchers'];
			}

			if (!empty($query['operation'])) {
				return $query;
			}
			return $query;
		}

		if (!empty($query['operation'])) {
			return $results;
		}

		foreach ($results as $i => $result) {
			$results[$i]['Package']['description'] = trim($results[$i]['Package']['description']);
			if (empty($results[$i]['Package']['description'])) {
				$results[$i]['Package']['description'] = 'No description available';
			}
		}

		return $results;
	}

	public function _findListformaintainer($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Invalid package'));
			}

			$query['conditions'] = array("{$this->alias}.maintainer_id" => $query[0]);
			$query['fields'] = array("{$this->alias}.{$this->primaryKey}", "{$this->alias}.{$this->displayField}");
			$query['order'] = array("{$this->alias}.{$this->displayField} DESC");
			$query['recursive'] = -1;
			return $query;
		}

		if (empty($results)) {
			return array();
		}
		return Set::combine(
			$results,
			"{n}.{$this->alias}.{$this->primaryKey}",
			"{n}.{$this->alias}.{$this->displayField}"
		);
	}

/**
 * Find a ratable package
 *
 * @param string $state
 * @param array $query
 * @param array $results
 * @return array
 * @todo Require that the user not own the package being rated
 */
	public function _findRate($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[$this->primaryKey])) {
				throw new NotFoundException(__("Cannot like a non-existent package"));
			}

			if (empty($query['user_id'])) {
				throw new UnauthorizedException(__("You must be logged in in order to rate packages"));
			}

			$query['conditions'] = array(
				"{$this->alias}.{$this->primaryKey}" => $query[$this->primaryKey],
			);
			$query['limit'] = 1;

			$query['fields'] = array($this->primaryKey);
			if (!empty($query['fields'])) {
				$query['fields'] = array_merge(
					$this->getDataSource()->fields($this, null, $query['fields']),
					$this->Rating->getDataSource()->fields($this->Rating)
				);
			} else {
				$query['fields'] = array_merge(
					$this->getDataSource()->fields($this),
					$this->Rating->getDataSource()->fields($this->Rating)
				);
			}

			$this->unbindModel(array(
				'hasMany' => array('Ratings.Rating'),
			));
			$query['joins'] = array(
				array(
					'alias' => 'Rating',
					'table' => 'ratings',
					'type' => 'LEFT',
					'conditions' => array(
						'Rating.model' => 'Package',
						'Rating.foreign_key' => $query['id'],
						'Rating.user_id' => $query['user_id'],
					),
				)
			);
			return $query;
		}

		if (empty($results[0])) {
			throw new NotFoundException(__("Cannot rate a non-existent package"));
		}

		if (empty($results[0]['Rating']['id'])) {
			$results[0]['Rating'] = false;
		}

		return $results[0];
	}

/**
 * Find repoclone type
 *
 * @param string $state
 * @param array $query
 * @param array $results
 * @return array
 */
	public function _findRepoclone($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Invalid package'));
			}

			$query['conditions'] = array("{$this->alias}.{$this->primaryKey}" => $query[0]);
			$query['contain'] = array('Maintainer.username');
			$query['fields'] = array(
				$this->primaryKey,
				'name',
				'repository_url',
				'maintainer_id',
			);
			$query['limit'] = 1;
			$query['order'] = array("{$this->alias}.{$this->primaryKey} ASC");
			return $query;
		}

		if (empty($results[0])) {
			throw new NotFoundException(__('Invalid package'));
		}
		return $results[0];
	}

	public function _findUncategorized($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query['user_id'])) {
				throw new UnauthorizedException(__("You must be logged in in order to rate packages"));
			}

			$query['conditions'] = array("{$this->alias}.category_id" => null);
			if (!empty($query[$this->primaryKey])) {
				$query['conditions']["{$this->alias}.{$this->primaryKey} <>"] = $query[$this->primaryKey];
			}

			$query['contain'] = array('Maintainer');
			$query['limit'] = 1;
			$query['order'] = array("{$this->alias}.{$this->primaryKey} ASC");
			return $query;
		}

		if (empty($results[0])) {
			throw new NotFoundException(__('No more uncategorized packages'));
		}
		return $results[0];
	}

	public function _findView($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query['maintainer']) || empty($query['package'])) {
				throw new InvalidArgumentException(__('Invalid package'));
			}

			$query['conditions'] = array(
				"{$this->alias}.{$this->displayField}" => $query['package'],
				"Maintainer.username" => $query['maintainer'],
			);
			$query['contain'] = array('Maintainer' => array('name', 'username'));
			$query['limit'] = 1;

			if (!empty($query['fields'])) {
				$query['fields'] = array_merge(
					$this->getDataSource()->fields($this, null, $query['fields']),
					$this->Category->getDataSource()->fields($this->Category)
				);
			} else {
				$query['fields'] = array_merge(
					$this->getDataSource()->fields($this),
					$this->Category->getDataSource()->fields($this->Category)
				);
			}

			$this->unbindModel(array(
				'belongsTo' => array('Categories.Category'),
			));

			$query['joins'] = array(
				array(
					'alias' => 'Category',
					'table' => 'categories',
					'type' => 'LEFT',
					'conditions' => array(
						'`Category`.`id` = `Package`.`category_id`'
					),
				),
			);

			// Join additional records if necessary
			if ($query['user_id']) {
				if (!empty($query['fields'])) {
					$query['fields'] = array_merge(
						$this->getDataSource()->fields($this, null, $query['fields']),
						$this->Rating->getDataSource()->fields($this->Rating),
						$this->Favorite->getDataSource()->fields($this->Favorite)
					);
				} else {
					$query['fields'] = array_merge(
						$this->getDataSource()->fields($this),
						$this->Rating->getDataSource()->fields($this->Rating),
						$this->Favorite->getDataSource()->fields($this->Favorite)
					);
				}

				$this->unbindModel(array(
					'hasMany' => array('Ratings.Rating', 'Favorites.Favorite'),
				));

				$query['joins'][] = array(
					'alias' => 'Favorite',
					'table' => 'favorites',
					'type' => 'LEFT',
					'conditions' => array(
						'`Favorite`.`foreign_key` = `Package`.`id`',
						'Favorite.model' => 'Package',
						'Favorite.type' => 'bookmark',
						'Favorite.user_id' => $query['user_id'],
					),
				);
				$query['joins'][] = array(
					'alias' => 'Rating',
					'table' => 'ratings',
					'type' => 'LEFT',
					'conditions' => array(
						'`Rating`.`foreign_key` = `Package`.`id`',
						'Rating.model' => 'Package',
						'Rating.user_id' => $query['user_id'],
					),
				);
			}
			return $query;
		}

		if (empty($results[0])) {
			throw new NotFoundException(__('Invalid package'));
		}

		if (empty($results[0]['Maintainer'])) {
			throw new NotFoundException(__('Invalid maintainer'));
		}

		if ($results[0]['Maintainer']['username'] !== $query['maintainer']) {
			throw new NotFoundException(__('Wrong Maintainer'));
		}

		if (empty($results[0]['Favorite']['id'])) {
			$results[0]['Favorite'] = false;
		}

		if (empty($results[0]['Rating']['id'])) {
			$results[0]['Rating'] = false;
		}

		DebugTimer::start('app.Package::rss', __d('app', 'Package::rss()'));
		list($results[0]['Rss'], $results[0]['Cache']) = $this->rss($results[0]);
		DebugTimer::stop('app.Package::rss');
		return $results[0];
	}

/**
 * Setup a repo
 *
 * @param integer $id
 * @return array
 */
	public function setupRepository($id = null) {
		if (!$id) {
			return false;
		}

		$package = $this->find('repoclone', $id);
		if (!$package) {
			return false;
		}

		if (!$this->_Folder) {
			$this->_Folder = new Folder();
		}

		$path = rtrim(trim(TMP), DS) . DS
				. 'repos' . DS
				. strtolower($package['Maintainer']['username'][0]) . DS
				. $package['Maintainer']['username'];
		if (!file_exists($path)) {
			$this->_Folder->create($path);
		}

		$this->_Folder->cd($path);
		$read = $this->_Folder->read();

		if (!in_array($package['Package']['name'], $read['0'])) {
			$paths = Configure::read('paths');
			if ($paths) {
				putenv('PATH=' . implode(':', $paths) . ':' . getenv('PATH'));
			}
			$var = $this->_shell_exec(sprintf("cd %s && git clone %s %s%s%s 2>&1 1> /dev/null",
				$path,
				$package['Package']['repository_url'],
				$path,
				DS,
				$package['Package']['name']
			));

			if (stristr($var, 'fatal')) {
				$this->log($var);
				return false;
			}
		}

		$var = $this->_shell_exec(sprintf("cd %s && git pull",
			$path . DS . $package['Package']['name']
		));
		if (stristr($var, 'fatal')) {
			$this->log($var);
			return false;
		}

		return array($package[$this->alias][$this->primaryKey], $path . DS . $package[$this->alias][$this->displayField]);
	}

/**
 * Mark a package as deleted/broken
 *
 * @param integer $id
 * @return boolean
 */
	public function broken($id) {
		$this->id = $id;
		return $this->saveField('deleted', true);
	}

/**
 * Enable/disable a package by toggle
 *
 * @param integer $id
 * @param boolean $enable
 * @return boolean true if enabled or false if disabled
 */
	public function enable($id = null, $enable = null) {
		if (isset($id)) {
			$this->id = $id;
		}
		if (isset($enable)) {
			if ($enable) {
				$this->undelete($this->id);
				return true;
			} else {
				$this->saveField('deleted', true);
				return false;
			}
		}
		$this->enableSoftDeletable(array('find'), false);
		$package = $this->findById($id);
		$this->enableSoftDeletable(array('find'), true);
		if ($package) {
			if ($package[$this->alias]['deleted']) {
				return $this->enable($this->id, true);
			} else {
				return $this->enable($this->id, false);
			}
		}
		return null;
	}

/**
 * Characterize a package
 *
 * @param integer $id
 * @return array
 */
	public function characterize($id) {
		$this->Behaviors->detach('Softdeletable');
		list($package_id, $path) = $this->setupRepository($id);
		if (!$package_id || !$path) {
			return !$this->broken($id);
		}

		$characterizer = new Characterizer($path);
		$data = $characterizer->classify();
		$this->create(false);
		return $this->save(array('Package' => array_merge(
			$data, array($this->primaryKey => $package_id, 'deleted' => false)
		)));
	}

/**
 * Fix a repo URL
 *
 * @param mixed $package
 * @return array
 */
	public function fixRepositoryUrl($package = null) {
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
		$package[$this->alias]['repository_url'][]	  = "git://github.com";
		$package[$this->alias]['repository_url'][]	  = $package['Maintainer']['username'];
		$package[$this->alias]['repository_url'][]	  = $package[$this->alias]['name'];
		$package[$this->alias]['repository_url']	= implode("/", $package[$this->alias]['repository_url']);
		$package[$this->alias]['repository_url']   .= '.git';
		return $this->save($package);
	}

/**
 * Categorizes a package. Packages can only be in a single category
 *
 * @return boolean
 **/
	public function categorizePackage($data = array()) {
		if (empty($data[$this->alias])) {
			throw new NotFoundException(__("Cannot bookmark a non-existent package"));
		}

		$id = null;
		$category_id = null;

		if (!empty($data[$this->alias][$this->primaryKey])) {
			$id = $data[$this->alias][$this->primaryKey];
		}

		if (!empty($data[$this->alias]['category_id'])) {
			$category_id = $data[$this->alias]['category_id'];
		}

		if (!$id && $this->id) {
			$id = $this->id;
		}

		if (!$id) {
			throw new NotFoundException(__("Cannot bookmark a non-existent package"));
		}

		if (!$category_id) {
			throw new UnauthorizedException(__("Invalid category"));
		}

		$package = $this->find('category', compact('id'));
		if ($package['Package']['category_id'] == $category_id) {
			return $id;
		} else {
			$this->id = $id;
	        if ($this->saveField('category_id', $category_id)) {
	        	return $id;
	        }
	    }

	    throw new BadRequestException(__("Unable to categorize package #%d", $id));
	}
/**
 * Favorites a package for the specified user
 *
 * @param int $id Package ID
 * @param int $user_id ID referencing a specific User
 * @return boolean
 **/
	public function favoritePackage($id = null, $user_id = null) {
		if (!$id && $this->id) {
			$id = $this->id;
		}

		if (!$id) {
			throw new NotFoundException(__("Cannot bookmark a non-existent package"));
		}

		if (!$user_id) {
			throw new UnauthorizedException(__("You must be logged in in order to bookmark packages"));
		}

		$action = 'add';
		$package = $this->find('bookmark', compact('id', 'user_id'));
		if ($package['Favorite']) {
			$action = 'remove';
			$result = $this->Favorite->deleteAll(array(
				'Favorite.user_id' => $user_id,
				'Favorite.model' => $this->name,
				'Favorite.type' => 'bookmark',
				'Favorite.foreign_key' => $id,
			), false, false);
			if ($result !== false) {
				return false;
			}
		} else {
			$result = $this->saveFavorite($user_id, $this->name, 'bookmark', $id);
			if ($result) {
				return true;
			}
		}

		throw new BadRequestException(__("Unable to %s bookmark", $action));
	}

/**
 * Actually rates a package
 *
 * @param int $id Package ID
 * @param int $user_id ID referencing a specific User
 * @param string $rating either "up" or "down"
 * @return boolean
 */
	public function ratePackage($id = null, $user_id = null) {
		if (!$id && $this->id) {
			$id = $this->id;
		}

		if (!$id) {
			throw new NotFoundException(__("Cannot like a non-existent package"));
		}

		if (!$user_id) {
			throw new UnauthorizedException(__("You must be logged in in order to like packages"));
		}

		$action = 'like';
		$package = $this->find('rate', compact('id', 'user_id'));
		if ($package['Rating']) {
			$action = 'dislike';
			$result = $this->removeRating($id, $user_id);
			if ($result !== false) {
				return false;
			}
		} else {
			$result = $this->saveRating($id, $user_id, 1);
			if ($result) {
				return true;
			}
		}

		throw new BadRequestException(__("Unable to %s package", $action));
	}

/**
 * Update Attributes from Github
 *
 * @param array $package
 * @return array
 */
	public function updateAttributes($package) {
		if (!$this->_Github) {
			$this->_Github = ClassRegistry::init('Github');
		}

		$repo = $this->_Github->find('repository', array(
			'owner' => $package['Maintainer']['username'],
			'repo' => $package['Package']['name'],
		));
		if (empty($repo) || !isset($repo['Repository'])) {
			return false;
		}

		// Detect homepage
		$homepage = $repo['Repository']['html_url'];
		if (!empty($repo['Repository']['homepage'])) {
			if (is_array($repo['Repository']['homepage'])) {
				$homepage = $repo['Repository']['homepage'];
			} else {
				$homepage = $repo['Repository']['homepage'];
			}
		} else if (!empty($repo['Repsitory']['homepage'])) {
			$homepage = $repo['Repository']['homepage'];
		}

		// Detect issues
		$issues = null;
		if ($repo['Repository']['has_issues']) {
			$issues = $repo['Repository']['open_issues'];
		}

		// Detect total contributors
		$contribs = 1;
		$contributors = $this->_Github->find('repository', array(
			'owner' => $package['Maintainer']['username'],
			'repo' => $package['Package']['name'],
			'_action' => 'contributors'
		));
		if (!empty($contributors)) {
			$contribs = count($contributors);
		}

		$collabs = 1;
		$collaborators = $this->_Github->find('repository', array(
			'owner' => $package['Maintainer']['username'],
			'repo' => $package['Package']['name'],
			'_action' => 'collaborators',
		));

		if (!empty($collaborators)) {
			$collabs = count($collaborators);
		}

		if (isset($repo['Repository']['description'])) {
			$package['Package']['description'] = $repo['Repository']['description'];
		}

		if (!empty($homepage)) {
			$package['Package']['homepage'] = $homepage;
		}
		if ($collabs !== null) {
			$package['Package']['collaborators'] = $collabs;
		}
		if ($contribs !== null) {
			$package['Package']['contributors'] = $contribs;
		}
		if ($issues !== null) {
			$package['Package']['open_issues'] = $issues;
		}

		$package['Package']['forks'] = $repo['Repository']['forks'];
		$package['Package']['watchers'] = $repo['Repository']['watchers'];
		$package['Package']['created_at'] = substr(str_replace('T', ' ', $repo['Repository']['created_at']), 0, 20);
		$package['Package']['last_pushed_at'] = substr(str_replace('T', ' ', $repo['Repository']['pushed_at']), 0, 20);

		$this->create();
		return $this->save($package);
	}

/**
 * Find a package on Github
 *
 * @param integer $package
 * @return boolean
 */
	public function findOnGithub($package = null) {
		if (!is_array($package)) {
			$package = $this->find('first', array(
				'conditions' => array("{$this->alias}.{$this->primaryKey}" => $package),
				'contain' => array('Maintainer' => array('fields' => 'username')),
				'fields' => array('name', 'repository_url')
			));
		}

		if (!$package) {
			return false;
		}

		if (!$this->_Github) {
			$this->_Github = ClassRegistry::init('Github');
		}

		$response = $this->_Github->find('repository', array(
			'owner' => $package['Maintainer']['username'],
			'repo' => $package[$this->alias]['name']
		));

		return !empty($response);
	}

/**
 * Clean Parameters
 *
 * @param array $named
 * @param array $options
 * @return array
 */
	public function cleanParams($named, $options = array()) {
		$coalesce = '';

		if (empty($named)) {
			return array(array(), $coalesce);
		}
		if (is_bool($options)) {
			$options = array('rinse' => $options);
		}

		$options = array_merge(array(
			'allowed' => array(),
			'coalesce' => false,
			'rinse' => array(
				'search' => ' ',
				'replace' => ' ',
			),
			'trim' => " \t\n\r\0\x0B+\"",
		), $options);

		if ($options['rinse'] === true) {
			$options['rinse'] = array(
				'search' => '+',
				'replace' => ' ',
			);
		}

		if (!empty($options['allowed'])) {
			$named = array_intersect_key($named, array_combine($options['allowed'], $options['allowed']));
		}

		if (isset($named['query']) && is_string($named['query']) && strlen($named['query'])) {
			$named['query'] = str_replace('\'', '"', $named['query']);
			preg_match_all('/\s*(\w+):\s*("[^"]*"|[^"\s]+)/', $named['query'], $matches, PREG_SET_ORDER);

			$query = preg_replace('/\s*(\w+):\s*("[^"]*"|[^"\s]+)/', '', $named['query']);
			if ($query === null) {
				$query = '';
			}

			$query = ' ' . trim($query, $options['trim']);
			foreach ($matches as $k => $value) {
				$key = strtolower($value[1]);
				if (!in_array($key, $options['allowed'])) {
					$query .= ' ' . $key . ':' . $value[2];
					continue;
				}

				if (isset($named[$key]) && $key == 'has') {
					if (is_array($named[$key])) {
						$named[$key][] = trim($value[2], $options['trim']);
					} elseif (isset($named[$key])) {
						$named[$key] = array(
							$named[$key],
							trim($value[2], $options['trim'])
						);
					}
				} else {
					$named[$key] = trim($value[2], $options['trim']);
				}
			}

			$named['query'] = trim($query, $options['trim']);
		}

		foreach ($named as $key => $value) {
			if (is_array($value)) {
				$values = array();
				foreach ($value as $v) {
					$values[] = str_replace(
						$options['rinse']['search'],
						$options['rinse']['replace'],
						Sanitize::clean($v)
					);
				}
				$named[$key] = $values;
			} else {
				$named[$key] = str_replace(
					$options['rinse']['search'],
					$options['rinse']['replace'],
					Sanitize::clean($value)
				);
			}
		}

		if ($options['coalesce']) {
			foreach ($named as $key => $value) {
				if ($key == 'query') {
					continue;
				}

				if (is_array($value)) {
					foreach ($value as $v) {
						if (strstr($v, ' ') !== false) {
							$coalesce .= " {$key}:\"{$v}\"";
						} else {
							$coalesce .= " {$key}:{$v}";
						}
					}
				} else {
					if (strstr($value, ' ') !== false) {
						$coalesce .= " {$key}:\"{$value}\"";
					} else {
						$coalesce .= " {$key}:{$value}";
					}
				}
			}

			$coalesce = trim($coalesce, $options['trim']);
			if (isset($named['query'])) {
				$coalesce = trim($named['query'], $options['trim']) . ' ' . $coalesce;
			}
		}

		$clean = array();
		foreach ($named as $key => $value) {
			if (is_array($value)) {
				$clean[$key] = $value;
			}

			if (is_string($value) && strlen($value)) {
				$clean[$key] = $value;
			}
		}
		$named = $clean;

		return array($named, trim($coalesce));
	}

/**
 * Categories
 *
 * @param integer $user_id
 * @return array
 */
	public function categories($user_id = null) {
		$categories = $this->Category->find('list', array(
			'order' => array('Category.name')
		));
		$diff = array_diff($this->_categories, $categories);
		if (!empty($diff)) {
			if (!$user_id) {
				throw new UnauthorizedException(__('You must be logged in to add categories'));
			}
			$data = array();
			foreach ($diff as $name) {
				$data[]['Category'] = compact('user_id', 'name');
			}
			$result = $this->Category->saveAll($data);
			if (!$result) {
				throw new OutOfBoundsException(__('Unable to create missing categories'));
			}
		}
		return $this->_categories = $this->Category->find('list', array(
			'order' => array('Category.name')
		));
	}

/**
 * Suggest a package
 *
 * @param array $data
 * @return array
 */
	public function suggest($data) {
		if (empty($data['github'])) {
			return false;
		}

		if (!preg_match('/(?:\/|\:)([\w-]+\/[\w-]+)/', $data['github'], $matches)) {
			if (!preg_match('/([\w-]+\/[\w-]+)/', $data['github'], $matches)) {
				return false;
			}
		}

		$pieces = explode('/', $matches[1]);
		if (count($pieces) < 2) {
			return false;
		}

		$ipaddress = $_SERVER['REMOTE_ADDR'];
		list($username, $repository) = $pieces;

		$data = compact('ipaddress', 'username', 'repository');
		if (!Resque::enqueue('default', 'SuggestPackageJob', array('work', $data))) {
			return false;
		}
		return array($username, $repository);
	}

/**
 * Get SEO for Package
 *
 * @param array $package
 * @return array
 */
	public function seoView($package) {
		$title = array();
		$title[] = Sanitize::clean($package['Package']['name'] . ' by ' . $package['Maintainer']['username']);
		$title[] = 'CakePHP Plugins and Applications';
		$title[] = 'CakePackages';
		$title = implode(' | ', $title);

		$description = Sanitize::clean($package['Package']['description']) . ' - CakePHP Package on CakePackages';

		$keywords = explode(' ', $package['Package']['name']);
		if (count($keywords) > 1) {
			$keywords[] = $package['Package']['name'];
		}
		$keywords[] = 'cakephp package';
		$keywords[] = 'cakephp';

		foreach ($this->_validTypes as $type) {
			if (isset($package['Package']['contains_' . $type]) && $package['Package']['contains_' . $type] == 1) {
				$keywords[] = $type;
			}
		}
		$keywords = implode(', ', $keywords);

		return array($title, $description, $keywords);
	}

/**
 * Get feed of package
 *
 * @param array $package
 * @param array $options
 * @return array
 */
	public function rss($package, $options = array()) {
		$options = array_merge(array(
			'allowed' => array('id', 'link', 'title', 'updated'),
			'cache' => null,
			'limit' => 4,
			'key' => null,
			'uri' => null,
		), $options);
		$options['allowed'] = array_combine($options['allowed'], $options['allowed']);

		if (!is_array($options['cache'])) {
			$options['cache'] = array(
				'key' => 'package.rss.' . md5($package['Maintainer']['username'] . $package['Package']['name']),
				'time' => '+6 hours',
			);
		}

		if (!$options['uri']) {
			$options['uri'] = sprintf("https://github.com/%s/%s/commits/master.atom",
				$package['Maintainer']['username'],
				$package['Package']['name']
			);
		}

		if (!$options['key']) {
			$options['key'] = md5($options['uri']);
		}

		$items = array();
		if (($items = Cache::read($options['key'])) !== false) {
			return array($items, $options['cache']);
		}

		if (!$this->_HttpSocket) {
			$this->_HttpSocket = new HttpSocket();
		}

		if (!$this->_HttpSocket) {
			return array($items, $options['cache']);
		}

		$result = $this->_HttpSocket->request(array('uri' => $options['uri']));
		$code = $this->_HttpSocket->response['status']['code'];
		$isError = is_array($result) && isset($result['Html']);

		if ($code != 404  && $result && !$isError) {
			$xmlError = libxml_use_internal_errors(true);
			$result = simplexml_load_string($result['body']);
			libxml_use_internal_errors($xmlError);
		}

		try {
			if ($result) {
				$result = Xml::toArray($result);
			}
		} catch (Exception $e) {
			return array($items, $options['cache']);
		}

		if (!empty($result['feed']['entry'])) {
			$result = array($result['feed']['entry']);
			if (!empty($result[0][0])) {
				$result = $result[0];
			} elseif (empty($result[0])) {
				$result = array($result);
			}

			$result = array_slice($result, 0, $options['limit'], true);

			foreach ($result as $item) {
				if (!empty($item['id'])) {
					$item['hash'] = explode("Commit/", $item['id']);
					$item['hash'] = end($item['hash']);
				} else {
					$item['hash'] = '';
				}

				if (!empty($item['title'])) {
					$item['title'] = Sanitize::clean($item['title']);
				} else {
					$item['title'] = 'Empty Commit Message';
				}

				if (!empty($item['link']['@href'])) {
					$item['link'] = $item['link']['@href'];
				} else {
					$item['link'] = '';
				}

				if (!empty($item['content']['@'])) {
					$item['content'] = $item['content']['@'];
				} else {
					$item['content'] = '';
				}

				if (!empty($item['media:thumbnail']['@url'])) {
					$item['avatar'] = $item['media:thumbnail']['@url'];
					unset($item['media:thumbnail']);
				} else {
					$item['avatar'] = '';
				}

				if (is_array($options['allowed'])) {
					$item = array_intersect_key($item, $options['allowed']);
				}

				$items[] = $item;
			}
		}

		Cache::write($options['key'], $items);
		return array($items, $options['cache']);
	}

/**
 * Get disqus parameters for a package
 *
 * @param array $package
 * @return array
 */
	public function disqus($package = array()) {
		return array(
			'disqus_shortname' => Configure::read('Disqus.disqus_shortname'),
			'disqus_identifier' => $package[$this->alias][$this->primaryKey],
			'disqus_title' => Sanitize::clean(implode(' ', array(
				$package['Package']['name'],
				'by',
				$package['Maintainer']['username'],
			))),
			'disqus_url' => Router::url(array(
				'controller' => 'packages',
				'action' => 'view',
				$package['Maintainer']['username'],
				$package['Package']['name']
			), true),
		);
	}

/**
 * Get Next Page
 *
 * @param array $params
 * @param boolean $next
 * @return array
 */
	public function getNextPage($params, $next = true) {
		if ($next === false) {
			return false;
		}

		$params = (array) $params;

		if (empty($params['page'])) {
			$params['page'] = 2;
		} else {
			$params['page'] = (int) $params['page'] + 1;
		}

		return $params;
	}

/**
 * Wrapper for shell_exec() method for testing
 */
	protected function _shell_exec($cmd) {
		return shell_exec($cmd);
	}
}