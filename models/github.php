<?php
class Github extends AppModel {
    var $name = 'Github';
    var $useTable = false;
    var $useDbConfig = 'github';

    function __construct($id = false, $table = null, $ds = null) {
        parent::__construct($id, $table, $ds);

        $this->_findMethods['blobShow'] = true;
        $this->_findMethods['blobShowAll'] = true;
        $this->_findMethods['blobShowPath'] = true;

        $this->_findMethods['commitsList'] = true;
        $this->_findMethods['commitsShowPath'] = true;
        $this->_findMethods['commitsShowSha'] = true;

        $this->_findMethods['issuesComments'] = true;
        $this->_findMethods['issuesLabels'] = true;
        $this->_findMethods['issuesList'] = true;
        $this->_findMethods['issuesSearch'] = true;
        $this->_findMethods['issuesShow'] = true;

        $this->_findMethods['reposSearch'] = true;
        $this->_findMethods['reposShow'] = true;
        $this->_findMethods['reposShowSingle'] = true;
        $this->_findMethods['reposShowCollaborators'] = true;
        $this->_findMethods['reposShowContributors'] = true;
        $this->_findMethods['reposShowLanguages'] = true;
        $this->_findMethods['reposShowNetwork'] = true;
        $this->_findMethods['reposShowTags'] = true;
        $this->_findMethods['reposWatched'] = true;

        $this->_findMethods['treeShow'] = true;

        $this->_findMethods['userShow'] = true;
        $this->_findMethods['userShowFollowing'] = true;
        $this->_findMethods['userWatched'] = true;
        $this->_findMethods['userEmail'] = true;
    }

    function _findIssuesSearch($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo', 'term') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $query['state'] = (empty($query['state'])) ? 'open' : $query['state'];
            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
                $query['state'],
                $query['term'],
            ));
            unset($query['username'], $query['repo'], $query['state'], $query['term']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findIssuesComments($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo', 'number') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
                $query['number'],
            ));
            unset($query['username'], $query['repo'], $query['number']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findIssuesLabels($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
            ));
            unset($query['username'], $query['repo']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findIssuesList($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $query['state'] = (empty($query['state'])) ? 'open' : $query['state'];
            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
                $query['state'],
            ));
            unset($query['username'], $query['repo'], $query['state']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findIssuesShow($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo', 'number') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
                $query['number'],
            ));
            unset($query['username'], $query['repo'], $query['number']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findReposShow($state, $query, $results = array()) {
        if ($state == 'before') {
            return $query;
        } elseif ($state == 'after') {
            if (isset($results['Repository'])) {
                if (empty($results['Repository'])) {
                    $results = array();
                } else {
                    $results = array($results);
                }
            }
            return $results;
        }
    }

    function _findReposShowSingle($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
            ));
            unset($query['username'], $query['repo']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findReposShowCollaborators($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
            ));
            unset($query['username'], $query['repo']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findReposShowContributors($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
            ));
            unset($query['username'], $query['repo']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findReposShowNetwork($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
            ));
            unset($query['username'], $query['repo']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findReposShowLanguages($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
            ));
            unset($query['username'], $query['repo']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findReposShowTags($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
            ));
            unset($query['username'], $query['repo']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findCommitsList($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $params['branch'] = (empty($params['branch'])) ? 'master' : $params['branch'];

            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
                $query['branch'],
            ));
            unset($query['username'], $query['repo'], $query['branch']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findCommitsShowPath($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo', 'path') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $params['branch'] = (empty($params['branch'])) ? 'master' : $params['branch'];

            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
                $query['branch'],
                $query['path'],
            ));
            unset($query['username'], $query['repo'], $query['branch'], $query['path']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findCommitsShowSha($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo', 'sha') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
                $query['sha'],
            ));
            unset($query['username'], $query['repo'], $query['sha']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findTreeShow($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo', 'sha') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
                $query['sha'],
            ));
            unset($query['username'], $query['repo'], $query['sha']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findBlobShow($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo', 'sha') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
                $query['sha'],
            ));
            unset($query['username'], $query['repo'], $query['sha']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findBlobShowAll($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo', 'sha', 'path') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
                $query['sha'],
                $query['path'],
            ));
            unset($query['username'], $query['repo'], $query['sha'], $query['path']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function _findBlobShowPath($state, $query, $results = array()) {
        if ($state == 'before') {
            foreach (array('username', 'repo', 'sha', 'path') as $param) {
                if (empty($query[$param])) {
                    throw new InvalidArgumentException(sprintf("Missing %s param", $param));
                }
            }

            $query['request'] = implode('/', array(
                $query['username'],
                $query['repo'],
                $query['sha'],
                $query['path'],
            ));
            unset($query['username'], $query['repo'], $query['sha'], $query['path']);
            return $query;
        } elseif ($state == 'after') {
            return $results;
        }
    }

    function get($method) {
        $params = func_get_args();
        array_shift($params);
        $method ='_get' . ucfirst($method);
        return call_user_func_array(array(&$this, $method), $params);
    }

    function _getNewRepositories($username = null) {
        if (!$username) return false;

        $repositories = $this->find('reposShow', $username);
        if (empty($repositories)) {
            return false;
        }

        $Maintainer = ClassRegistry::init('Maintainer');
        $existingUser = $Maintainer->find('view', $username);
        $packages = $Maintainer->Package->find('list', array('conditions' => array(
            'Package.maintainer_id' => $existingUser['Maintainer']['id'])
        ));

        $results = array();
        foreach ($repositories as $key => $repository) {
            if (!in_array($repository['Repository']['name'], $packages) && ($repository['Repository']['fork'] != 1)) {
                $results[] = $repository;
            }
        }
        return $results;
    }

    function _getUnlisted($username = 'josegonzalez') {
        $following = $this->find('usersShowFollowing', 'josegonzalez');
        ClassRegistry::init('Maintainer');
        $maintainer = &new Maintainer;
        $maintainers = $maintainer->find('list', array('fields' => array('username')));
        $maintainers = array_values($maintainers);
        foreach ($following['Users']['User'] as $key => &$user) {
            if (in_array($user, $maintainers)) {
                unset($following['Users']['User'][$key]);
            }
        }
        return $following['Users']['User'];
    }

    function savePackage($username, $name) {
        App::import('Lib', 'NewPackageJob');
        return $this->enqueue(new NewPackageJob($username, $name));
    }

    function saveUser($username = null) {
        if (!$username) return false;

        App::import('Lib', 'NewMaintainerJob');
        return $this->enqueue(new NewMaintainerJob($username, $name));
    }

    function _getRelatedRepositories($maintainers = array()) {
        if (!$maintainers) return false;

        $Package = ClassRegistry::init('Package');
        foreach ($maintainers as $i => $maintainer) {
            $repos = $this->find('reposShow', $maintainer['Maintainer']['username']);
            $maintainers[$i]['Repository'] = array();
            if (!empty($repos)) {
                $packages = $Package->find('listformaintainer', $maintainer['Maintainer']['id']);

                foreach ($repos as $j => $repo) {
                    if (!in_array($repo['Repository']['name'], $packages) && $repo['Repository']['fork'] != true) {
                        $maintainers[$i]['Repository'][] = $repo['Repository'];
                    }
                }
            }
        }

        return $maintainers;
    }
}
?>