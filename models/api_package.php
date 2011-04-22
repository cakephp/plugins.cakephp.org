<?php
class ApiPackage extends AppModel {
    var $name = 'ApiPackage';
    var $belongsTo = array('Maintainer');
    var $hasOne = array('Source');
    var $useTable = 'packages';
    var $_findMethods = array(
        'install' => true
    );

    function _findInstall($state, $query, $results = array()) {
        if ($state == 'before') {
            $query['conditions'] = array(
                $this->alias . '.name LIKE' => $query['request']['package'],
                $this->alias . '.deleted' => false,
            );

            $query['fields'] = array('name', 'description', 'last_pushed_at');

            $contains = array();

            if (isset($query['request']['maintainer'])) {
                $query['conditions']['Maintainer.username LIKE'] = $query['request']['maintainer'];
                $contains['Maintainer'] = array('username', 'name');
            }

            if (isset($query['request']['type'])) {
                $query['conditions']['Source.type'] = $query['request']['type'];
                $query['conditions']['Source.deleted'] = false;
                $contains['Source'] = array('name', 'type', 'path', 'default', 'official');

                if (isset($query['request']['source'])) {
                    $query['conditions']['Source.name'] = $query['request']['source'];
                } else {
                    $query['conditions']['Source.default'] = $query['request']['type'];
                }
            } elseif (isset($query['request']['source'])) {
                $query['conditions']['Source.name'] = $query['request']['source'];
                $query['conditions']['Source.deleted'] = false;
                $contains['Source'] = array('name', 'type', 'path', 'default', 'official');
            } else {
                $contains['Source'] = array('name', 'type', 'path', 'default', 'official');
            }

            if (!empty($contains)) {
                $query['contain'] = $contains;
            }

            unset($query['request']);
            return $query;
        } elseif ($state == 'after') {
            if (empty($results)) {
                return false;
            }

            foreach ($results as &$result) {
                $result['Source']['default'] = (bool) $result['Source']['default'];
                $result['Source']['official'] = (bool) $result['Source']['official'];
                $result['Package'] = $result[$this->alias];
                unset($result[$this->alias], $result['Maintainer']['id']);
            }
            return $results;
        }
    }

}