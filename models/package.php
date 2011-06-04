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
    var $Github = null;
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
            if (empty($query['term'])) {
                throw new InvalidArgumentException(__('Invalid query', true));
            }

            if (!class_exists('Sanitize')) {
                App::import('Core', 'Sanitize');
            }

            $query['term'] = Sanitize::clean($query['term']);
            $query['cache'] = true;
            $query['conditions'] = array("{$this->alias}.{$this->displayField} LIKE" => "%{$query['term']}%");
            $query['contain'] = array('Maintainer' => array('username'));
            $query['fields'] = array($this->primaryKey, $this->displayField);
            $query['limit'] = 10;
            return $query;
        } elseif ($state == 'after') {
            $searchResults = array();
            foreach ($results as $package) {
                $searchResults[] = array(
                    'id'    => $package['Package']['id'],
                    'slug'  => sprintf("%s/%s", $package['Maintainer']['username'], $package['Package']['name']),
                    'value' => $package['Package']['name'],
                    "label" => preg_replace("/".$query['term']."/i", "<strong>$0</strong>", $package['Package']['name'])
                );
            }
            return json_encode($searchResults);
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
            if (!empty($query['paginateType']) && in_array($query['paginateType'], $this->validTypes)) {
                $query['conditions'] = array("{$this->alias}.contains_{$query['paginateType']}" => true);
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
            $query['contain'] = array('Maintainer' => array('id', 'username', 'name'));
            if (empty($query['is_paginate'])) {
                $query['fields'] = array("{$this->alias}.$this->displayField", "{$this->alias}.maintainer_id");
            } else {
                $query['fields'] = array_diff(
                    array_keys($this->schema()),
                    array('deleted', 'created', 'modified', 'repository_url', 'homepage', 'tags', 'bakery_article')
                );
            }
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
            $query['contain'] = array('Maintainer' => array('id', 'username', 'name'));
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
            $query['contain'] = array('Maintainer.username');
            $query['fields'] = array('id', 'name', 'repository_url');
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

    function afterSave($created) {
        if ($created === true) {
            $id = $this->getLastInsertID();
            $package = $this->setupRepository($id);
            if ($package) {
                $this->characterize($package);
            }
        }
    }

    function setupRepository($id = null) {
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
            $var = shell_exec(sprintf("cd %s && git clone %s %s%s%s 2>&1 1> /dev/null",
                $path,
                $package['Package']['repository_url'],
                $path,
                DS,
                $package['Package']['name']
            ));
            if (stristr($var, 'fatal')) return false;
        }

        $var = shell_exec(sprintf("cd %s && git pull",
            $path . DS . $package['Package']['name']
        ));
        if (stristr($var, 'fatal')) return false;

        return array($package['Package']['id'], $path . DS . $package['Package']['name']);
    }

    function characterize($id) {
        $this->getDatasource()->disconnect();
        $this->getDatasource()->connect();

        $this->enableSoftDeletable('find', false);
        list($id, $path) = $this->setupRepository($id);
        if (!$id || !$path) return false;

        if (!class_exists('PackageCharacteristics')) App::import('Lib', 'PackageCharacteristics');
        try {
            $characterizer = new PackageCharacteristics($path);
            $data = $characterizer->characterize();
            if (!$data) throw new Exception('Potentially not a CakePHP Repository');
        } catch (Exception $e) {
            printf("============== EXCEPTION %s\n", $e->getMessage());
            $this->broken($id);
            return false;
        }

        $data = array_merge($data, array('id' => $id, 'deleted' => 0));
        $this->create(false);
        $this->set(array('Package' => $data));
        return $this->save(array('Package' => $data));
    }

    function broken($id) {
        $this->delete($id);
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

    function updateAttributes($package) {
        if (!$this->Github) {
            $this->Github = ClassRegistry::init('Github');
        }

        $repo = $this->Github->find('reposShowSingle', array(
            'username' => $package['Maintainer']['username'],
            'repo' => $package['Package']['name']
        ));
        if (empty($repo) || !isset($repo['Repository'])) return false;

        // Detect homepage
        $homepage = (string) $repo['Repository']['url'];
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
        $contributors = $this->Github->find('reposShowContributors', array(
            'username' => $package['Maintainer']['username'], 'repo' => $package['Package']['name']
        ));
        if (!empty($contributors)) {
            $contribs = count($contributors);
        }

        $collabs = 1;
        $collaborators = $this->Github->find('reposShowCollaborators', array(
            'username' => $package['Maintainer']['username'], 'repo' => $package['Package']['name']
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

        $package[$this->alias]['repository_url']    = array();
        $package[$this->alias]['repository_url'][]    = "git://github.com";
        $package[$this->alias]['repository_url'][]    = $package['Maintainer']['username'];
        $package[$this->alias]['repository_url'][]    = $package[$this->alias]['name'];
        $package[$this->alias]['repository_url']    = implode("/", $package[$this->alias]['repository_url']);
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