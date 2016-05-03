<?php
App::uses('PackageData', 'Lib');
App::uses('DebugTimer', 'DebugKit.Lib');
App::uses('HttpSocket', 'Network/Http');
App::uses('Sanitize', 'Utility');
App::uses('Xml', 'Utility');
App::uses('Folder', 'Utility');

/**
 * Package Model
 */
class Package extends AppModel
{

    public $name = 'Package';

    public $actsAs = array(
        'Favorites.Favorite',
        'Ratings.Ratable' => array(
            'calculation' => 'sum',
            'modelClass' => 'Package',
            'update' => true,
        ),
        'SoftDeletable',
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
        'bookmark' => true,
        'category' => true,
        'download' => true,
        'home' => true,
        'index' => true,
        'listformaintainer' => true,
        'rate' => true,
        'redirect' => true,
        'uncategorized' => true,
        'unversioned' => true,
        'view' => true,
    );

    public static $allowedFilters = array(
        'collaborators', 'contains', 'contributors',
        'direction', 'forks', 'has', 'open_issues',
        'query', 'sort', 'since', 'watchers', 'with',
        'category', 'version'
    );

    public $categories = array(
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

    protected static $_categoryColors = array();

    protected static $_validOrders = array(
        'collaborators', 'contributors',
        'created', 'forks', 'last_pushed_at',
        'open_issues', 'watchers'
    );

    public static $validShownOrders = array(
        'username' => 'Name',
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
    public $validTypes = array(
        'model', 'controller', 'view',
        'behavior', 'component', 'helper',
        'shell', 'theme', 'datasource',
        'lib', 'test', 'vendor',
        'app', 'config', 'resource',
    );

    public $Github = null;

    public $HttpSocket = null;

    public function __construct($packageId = false, $table = null, $datasource = null)
    {
        parent::__construct($packageId, $table, $datasource);
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
            'ratings' => array('text' => __('Rating'), 'sort' => 'rating'),
            'watchers' => array('text' => __('Watchers'), 'sort' => 'watchers', 'direction' => 'desc'),
            'title' => array('text' => __('Title'), 'sort' => 'name'),
            'maintainer' => array('text' => __('Maintainer'), 'sort' => 'Maintainer.name'),
            'date' => array('text' => __('Date Created'), 'sort' => 'created_at'),
            'updated' => array('text' => __('Date Updated'), 'sort' => 'last_pushed_at'),
        );
    }

/**
 * Find a ratable package
 *
 * @param string $state Either "before" or "after"
 * @param array $query Query
 * @param array $results Results
 * @return array
 * @todo Require that the user not own the package being rated
 */
    protected function _findBookmark($state, $query, $results = array())
    {
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

    protected function _findCategory($state, $query, $results = array())
    {
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

    protected function _findDownload($state, $query, $results = array())
    {
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

    protected function _findHome($state, $query, $results = array())
    {
        if ($state == 'before') {
            $query['contain'] = array('Category', 'Maintainer');
            $query['fields'] = array(
                "{$this->alias}.{$this->primaryKey}", "{$this->alias}.name",
                "{$this->alias}.description", "{$this->alias}.watchers", "{$this->alias}.modified",
                'Category.name', 'Category.slug',
                'Maintainer.username',
            );
            $query['order'] = array('Maintainer.username ASC');
            return $query;
        }

        foreach ($results as $i => $result) {
            $results[$i]['Category']['color'] = '';
            if (!empty($result['Category']['slug'])) {
                $results[$i]['Category']['color'] = $this->packageColor($result['Category']['slug']);
            }
        }

        return $results;
    }

    public function findIndex($state, $query, $results = array())
    {
        return $this->_findIndex($state, $query, $results);
    }

    protected function _findIndex($state, $query, $results = array())
    {
        if ($state == 'before') {
            if (!isset($query['named'])) {
                $query['named'] = array();
            }
            $query['named'] = array_merge(array(
                'collaborators' => null,
                'contains' => array(),
                'contributors' => null,
                'forks' => null,
                'has' => array(),
                'open_issues' => null,
                'query' => null,
                'since' => null,
                'version' => null,
                'watchers' => null,
                'with' => array(),
            ), $query['named']);

            $query['named']['has'] = array_merge(
                (array)$query['named']['with'],
                (array)$query['named']['contains'],
                (array)$query['named']['has']
            );

            $query['conditions'] = array("{$this->alias}.deleted" => false);
            $query['contain'] = array('Maintainer');
            $query['fields'] = array(
                "{$this->alias}.{$this->primaryKey}", "{$this->alias}.name",
                "{$this->alias}.description", "{$this->alias}.watchers", "{$this->alias}.modified",
                'Category.name', 'Category.slug',
                'Maintainer.username',
            );

            $direction = 'asc';
            if (!empty($query['named']['direction'])) {
                $query['named']['direction'] = strtolower((string)$query['named']['direction']);
                if ($query['named']['direction'] == 'dsc' || $query['named']['direction'] == 'des') {
                    $query['named']['direction'] = 'desc';
                }

                if ($query['named']['direction'] != 'asc' && $query['named']['direction'] != 'desc') {
                    $query['named']['direction'] = 'desc';
                }
                $direction = $query['named']['direction'];
            }

            $sortField = 'username';
            if (!empty($query['named']['sort'])) {
                $query['named']['sort'] = strtolower($query['named']['sort']);
                if (in_array($query['named']['sort'], Package::$_validOrders)) {
                    $sortField = $query['named']['sort'];
                }
            }

            if ($sortField == 'username') {
                $query['order'] = array(array("Maintainer.{$sortField} {$direction}"));
            } else {
                $query['order'] = array(array("{$this->alias}.{$sortField} {$direction}"));
            }

            if ($query['named']['collaborators'] !== null) {
                $query['conditions']["{$this->alias}.collaborators >="] = (int)$query['named']['collaborators'];
            }

            if ($query['named']['contributors'] !== null) {
                $query['conditions']["{$this->alias}.contributors >="] = (int)$query['named']['contributors'];
            }

            if ($query['named']['forks'] !== null) {
                $query['conditions']["{$this->alias}.forks >="] = (int)$query['named']['forks'];
            }

            if (!empty($query['named']['has']) || !empty($query['named']['version'])) {
                foreach ($query['named']['has'] as $has) {
                    $has = inflector::singularize(strtolower($has));
                    if (in_array($has, $this->validTypes)) {
                        $query['conditions'][] = array(
                            'Tag.keyname' => $has,
                            'Tag.identifier' => 'contains',
                        );
                    }
                }

                if (!empty($query['named']['version'])) {
                    $query['conditions'][] = array(
                        'Tag.keyname LIKE' => $query['named']['version'] . '%',
                        'Tag.identifier' => 'version',
                    );
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
            } else {
                $query['contain'][] = 'Category';
            }

            if ($query['named']['open_issues'] !== null) {
                $query['conditions']["{$this->alias}.open_issues <="] = (int)$query['named']['open_issues'];
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
                $query['conditions']["{$this->alias}.watchers >="] = (int)$query['named']['watchers'];
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
            $results[$i]['Package']['description'] = trim($result['Package']['description']);
            if (empty($result['Package']['description'])) {
                $results[$i]['Package']['description'] = 'No description available';
            }

            $results[$i]['Category']['color'] = '';
            if (!empty($result['Category']['slug'])) {
                $results[$i]['Category']['color'] = $this->packageColor($result['Category']['slug']);
            }
        }

        return $results;
    }

    protected function _findListformaintainer($state, $query, $results = array())
    {
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
    protected function _findRate($state, $query, $results = array())
    {
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

    protected function _findRedirect($state, $query, $results = array())
    {
        if ($state == 'before') {
            if (empty($query['maintainer']) || empty($query['package'])) {
                throw new InvalidArgumentException(__('Invalid find params'));
            }

            $query['conditions'] = array(
                "{$this->alias}.{$this->displayField}" => $query['package'],
                "Maintainer.username" => $query['maintainer'],
            );
            $query['contain'] = array('Maintainer' => array('username'));
            return $query;
        }

        if (empty($results[0])) {
            throw new NotFoundException(__('Invalid package'));
        }
        return $results[0];
    }

    protected function _findUncategorized($state, $query, $results = array())
    {
        if ($state == 'before') {
            if (empty($query['user_id'])) {
                throw new UnauthorizedException(__("You must be logged in in order to categorize packages"));
            }

            $query['contain'] = array('Maintainer');
            $query['conditions'] = array("{$this->alias}.category_id" => null);
            if (!empty($query[$this->primaryKey])) {
                $query['conditions']["{$this->alias}.{$this->primaryKey} <>"] = $query[$this->primaryKey];
            }

            $query['contain'] = array('Maintainer');
            $query['limit'] = 1;
            $query['order'] = array('Maintainer.username ASC');
            return $query;
        }

        if (empty($results[0])) {
            throw new NotFoundException(__('No more uncategorized packages'));
        }
        return $results[0];
    }

    protected function _findUnversioned($state, $query, $results = array())
    {
        if ($state == 'before') {
            if (empty($query['user_id'])) {
                throw new UnauthorizedException(__("You must be logged in in order to version packages"));
            }

            $query['contain'] = array('Maintainer');
            $query['conditions'] = array("{$this->alias}.category_id" => null);
            if (!empty($query[$this->primaryKey])) {
                $query['conditions']["{$this->alias}.{$this->primaryKey} <>"] = $query[$this->primaryKey];
            }

            $query['contain'] = array('Maintainer');
            $query['limit'] = 1;
            $query['order'] = array('Maintainer.username ASC');
            return $query;
        }

        if (empty($results[0])) {
            throw new NotFoundException(__('No more uncategorized packages'));
        }
        return $results[0];
    }

    protected function _findView($state, $query, $results = array())
    {
        if ($state == 'before') {
            if (empty($query['package_id'])) {
                throw new InvalidArgumentException(__('Invalid package'));
            }

            $query['conditions'] = array(
                "{$this->alias}.{$this->primaryKey}" => $query['package_id'],
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

        if (empty($results[0]['Favorite']['id'])) {
            $results[0]['Favorite'] = false;
        }

        if (empty($results[0]['Rating']['id'])) {
            $results[0]['Rating'] = false;
        }

        if ($this->shouldForceUpdate($results[0][$this->alias]['modified'])) {
            try {
                $this->enqueue('UpdatePackageJob', array($results[0][$this->alias][$this->primaryKey]));
            } catch (Exception $e) {
                CakeLog::warning('Package::find(\'view\')' . $e->getMessage());
            }
        }

        DebugTimer::start('app.Package::rss', __d('app', 'Package::rss()'));
        list($results[0]['Rss'], $results[0]['Cache']) = $this->rss($results[0]);
        DebugTimer::stop('app.Package::rss');
        return $results[0];
    }

    public function shouldForceUpdate($lastModified)
    {
        $date = new DateTime();
        $lastModifiedDate = new DateTime($lastModified);
        $interval = $date->diff($lastModifiedDate);
        return $interval->days > 7;
    }

/**
 * Mark a package as deleted/broken
 *
 * @param integer $id
 * @return boolean
 */
    public function broken($packageId)
    {
        $this->id = $packageId;
        return $this->saveField('deleted', true);
    }

/**
 * Enable/disable a package by toggle
 *
 * @param integer $id
 * @param boolean $enable
 * @return boolean true if enabled or false if disabled
 */
    public function enable($packageId = null, $enable = null)
    {
        if ($packageId !== null) {
            $this->id = $packageId;
        }

        if (isset($enable)) {
            if ($enable) {
                $this->undelete($this->id);
                return true;
            }

            $this->saveField('deleted', true);
            return false;
        }

        $this->enableSoftDeletable(array('find'), false);
        $package = $this->findById($this->id);
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
 * Fix a repo URL
 *
 * @param mixed $package
 * @return array
 */
    public function fixRepositoryUrl($package = null)
    {
        if (!$package) {
            return false;
        }

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

        $package[$this->alias]['repository_url'] = array();
        $package[$this->alias]['repository_url'][] = "git://github.com";
        $package[$this->alias]['repository_url'][] = $package['Maintainer']['username'];
        $package[$this->alias]['repository_url'][] = $package[$this->alias]['name'];
        $package[$this->alias]['repository_url'] = implode("/", $package[$this->alias]['repository_url']);
        $package[$this->alias]['repository_url'] .= '.git';
        return $this->save($package);
    }

/**
 * Categorizes a package. Packages can only be in a single category
 *
 * @return boolean
 **/
    public function categorizePackage($data = array())
    {
        if (empty($data[$this->alias])) {
            throw new NotFoundException(__("Cannot bookmark a non-existent package"));
        }

        $packageId = Hash::get($data, "{$this->alias}.{$this->primaryKey}", null);
        $categoryId = Hash::get($data, "{$this->alias}.category_id", null);

        if (!$packageId && $this->id) {
            $packageId = $this->id;
        }

        if (!$packageId) {
            throw new NotFoundException(__("Cannot categorize a non-existent package"));
        }

        if (!$categoryId) {
            throw new UnauthorizedException(__("Invalid category"));
        }

        $package = $this->find('category', ['id' => $packageId]);
        if ($package['Package']['category_id'] == $categoryId) {
            return $packageId;
        }

        $this->id = $packageId;
        if ($this->saveField('category_id', $categoryId)) {
            return $packageId;
        }

        throw new BadRequestException(__("Unable to categorize package #%d", $packageId));
    }

/**
 * Favorites a package for the specified user
 *
 * @param int $packageId Package ID
 * @param int $userId ID referencing a specific User
 * @return boolean
 **/
    public function favoritePackage($packageId = null, $userId = null)
    {
        if (!$packageId && $this->id) {
            $packageId = $this->id;
        }

        if (!$packageId) {
            throw new NotFoundException(__("Cannot bookmark a non-existent package"));
        }

        if (!$userId) {
            throw new UnauthorizedException(__("You must be logged in in order to bookmark packages"));
        }

        $action = 'add';
        $package = $this->find('bookmark', array(
            'id' => $packageId,
            'user_id' => $userId,
        ));
        if ($package['Favorite']) {
            $action = 'remove';
            $result = $this->Favorite->deleteAll(array(
                'Favorite.user_id' => $userId,
                'Favorite.model' => $this->name,
                'Favorite.type' => 'bookmark',
                'Favorite.foreign_key' => $packageId,
            ), false, false);
            if ($result !== false) {
                return false;
            }
        } else {
            $result = $this->saveFavorite($userId, $this->name, 'bookmark', $packageId);
            if ($result) {
                return true;
            }
        }

        throw new BadRequestException(__("Unable to %s bookmark", $action));
    }

/**
 * Actually rates a package
 *
 * @param int $packageId Package ID
 * @param int $userId ID referencing a specific User
 * @param string $rating either "up" or "down"
 * @return boolean
 */
    public function ratePackage($packageId = null, $userId = null)
    {
        if (!$packageId && $this->id) {
            $packageId = $this->id;
        }

        if (!$packageId) {
            throw new NotFoundException(__("Cannot like a non-existent package"));
        }

        if (!$userId) {
            throw new UnauthorizedException(__("You must be logged in in order to like packages"));
        }

        $action = 'like';
        $package = $this->find('rate', array(
            'id' => $packageId,
            'user_id' => $userId
        ));
        if ($package['Rating']) {
            $action = 'dislike';
            $result = $this->removeRating($packageId, $userId);
            if ($result !== false) {
                return false;
            }
        } else {
            $result = $this->saveRating($packageId, $userId, 1);
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
    public function updateAttributes($package, $packageData = null)
    {
        if (!$this->Github) {
            $this->Github = ClassRegistry::init('Github');
        }

        if ($packageData === null) {
            $packageData = new PackageData(
                $package['Maintainer']['username'],
                $package[$this->alias]['name'],
                $this->Github
            );
        }

        $data = $packageData->retrieve();
        if ($data === false) {
            return;
        }

        foreach ($data as $key => $value) {
            if ($value !== null) {
                $package[$this->alias][$key] = $value;
            }
        }

        $packageData = $package[$this->alias];
        unset($packageData['modified']);

        $this->create();
        return $this->save($packageData);
    }

/**
 * Find a package on Github
 *
 * @param integer $package
 * @return boolean
 */
    public function findOnGithub($package = null)
    {
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

        if (!$this->Github) {
            $this->Github = ClassRegistry::init('Github');
        }

        $response = $this->Github->find('repository', array(
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
    public function cleanParams($named, $options = array())
    {
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
            foreach ($matches as $value) {
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

    public function versions($userId = null)
    {
        return array(
            '2.x',
            '3.x',
        );
    }

/**
 * Categories
 *
 * @param integer $userId
 * @return array
 */
    public function categories($userId = null)
    {
        $categories = $this->Category->find('list', array(
            'order' => array('Category.name')
        ));
        $diff = array_diff($this->categories, $categories);
        if (!empty($diff)) {
            if (!$userId) {
                throw new UnauthorizedException(__('You must be logged in to add categories'));
            }
            $data = array();
            foreach ($diff as $name) {
                $data[]['Category'] = array(
                    'user_id' => $userId,
                    'name' => $name,
                );
            }
            $result = $this->Category->saveAll($data);
            if (!$result) {
                throw new OutOfBoundsException(__('Unable to create missing categories'));
            }
        }
        return $this->categories = $this->Category->find('list', array(
            'order' => array('Category.name')
        ));
    }

/**
 * Suggest a package
 *
 * @param array $data
 * @return array
 */
    public function suggest($data)
    {
        if (empty($data['github'])) {
            return false;
        }

        if (!preg_match('/([\w-]+\/[\w-]+)(?:\.git)?$/', $data['github'], $matches)) {
            return false;
        }

        $pieces = explode('/', $matches[1]);
        if (count($pieces) < 2) {
            return false;
        }

        $ipaddress = null;
        if (isset($_SERVER['REMOTE_ADDR'], $ipaddress)) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        }

        list($username, $repository) = $pieces;

        $data = compact('ipaddress', 'username', 'repository');
        if (!$this->enqueue('SuggestPackageJob', array($data))) {
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
    public function seoView($package)
    {
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

        foreach ($this->validTypes as $type) {
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
    public function rss($package, $options = array())
    {
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
            $options['uri'] = sprintf(
                "https://github.com/%s/%s/commits/master.atom",
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

        if (!$this->HttpSocket) {
            $this->HttpSocket = new HttpSocket();
        }

        if (!$this->HttpSocket) {
            return array($items, $options['cache']);
        }

        try {
            $result = $this->HttpSocket->request(array('uri' => $options['uri']));
        } catch (SocketException $e) {
            return array($items, $options['cache']);
        }

        $code = $this->HttpSocket->response['status']['code'];
        $isError = is_array($result) && isset($result['Html']);
        if ($code != 404 && $result && !$isError) {
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
    public function disqus($package = array())
    {
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

    public function stringToColor($text, $minBrightness = 50, $spec = 9)
    {
        // Check inputs
        if (!is_int($minBrightness)) {
            throw new Exception("$minBrightness is not an integer");
        }
        if (!is_int($spec)) {
            throw new Exception("$spec is not an integer");
        }
        if ($spec < 2 || $spec > 10) {
            throw new Exception("$spec is out of range");
        }
        if ($minBrightness < 0 || $minBrightness > 255) {
            throw new Exception("$minBrightness is out of range");
        }

        $hash = md5($text); // Gen hash of text
        $colors = array();
        for ($i = 0; $i < 3; $i++) {
            // convert hash into 3 decimal values between 0 and 255
            $colors[$i] = max(array(round(((hexdec(substr($hash, $spec * $i, $spec))) / hexdec(str_pad('', $spec, 'F'))) * 255), $minBrightness));
        }

        // only check brightness requirements if minBrightness is about 100
        if ($minBrightness > 0) {
            // loop until brightness is above or equal to minBrightness
            while (array_sum($colors) / 3 < $minBrightness) {
                for ($i = 0; $i < 3; $i++) {
                    $colors[$i] += 10;  // increase each color by 10
                }
            }
        }

        $output = '';

        for ($i = 0; $i < 3; $i++) {
            // convert each color to hex and append to output
            $output .= str_pad(dechex($colors[$i]), 2, 0, STR_PAD_LEFT);
        }

        return '#' . $output;
    }

/**
 * Get Next Page
 *
 * @param array $params
 * @param bool $next
 * @return array
 */
    public function getNextPage($params, $next = true)
    {
        if ($next === false) {
            return false;
        }

        $params = (array)$params;

        if (empty($params['page'])) {
            $params['page'] = 2;
        } else {
            $params['page'] = (int)$params['page'] + 1;
        }

        return $params;
    }

    public function packageColor($slug)
    {
        if (empty(static::$_categoryColors[$slug])) {
            static::$_categoryColors[$slug] = $this->stringToColor($slug);
        }

        return static::$_categoryColors[$slug];
    }
}
