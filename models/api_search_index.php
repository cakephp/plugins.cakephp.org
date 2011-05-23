<?php
class ApiSearchIndex extends AppModel {
    var $name = 'ApiSearchIndex';
    var $useTable = 'search_index';
    var $_findMethods = array(
        'search' => true, 'types' => true
    );

    function getHashedMapping() {
        $response = array();
        $Package = ClassRegistry::init('Package');
        foreach (array_keys($Package->schema()) as $field) {
            $response['Package.' . $field] = sha1('Package.' . $field);
        }
        foreach (array_keys($Package->Maintainer->schema()) as $field) {
            $response['Maintainer.' . $field] = sha1('Maintainer.' . $field);
        }
        return $response;
    }

/**
 * Returns array of types (models) used in the Search Index with model name as
 * the key and the humanised form as the value.
 *
 * @return array
 */
    function getTypes() {
        if (($types = Cache::read('app_search_index_types')) !== false) {
            return $types;
        }
         return $this->find('types');
     }

    function _findSearch($state, $query, $results = array()) {
        if ($state == 'before') {
            $query['conditions'] = array(
                array($this->alias . '.active' => 1),
                'or' => array(
                    array($this->alias . '.published' => null),
                    array($this->alias . '.published <= ' => date('Y-m-d H:i:s'))
                )
            );

            if (!empty($query['type'])) {
                $query['conditions']['model'] = $query['type'];
            }

            $term = implode(' ', array_map(array($this, 'replace'), preg_split('/[\s_]/', $query['term']))) . '*';
            if (!empty($query['like'])) {
                $query['conditions'][] = array('or' => array(
                        "MATCH(data) AGAINST('$term')",
                        $this->alias . '.data LIKE' => "%{$query['term']}%"
                ));
            } else {
                $query['conditions'][] = "MATCH(data) AGAINST('{$query['term']}' IN BOOLEAN MODE)";
            }

            if (empty($query['fields'])) {
                $query['fields'] = array(
                    '`foreign_key` as `id`',
                    'name',
                    'summary',
                    "MATCH(data) AGAINST('{$query['term']}' IN BOOLEAN MODE) AS score"
                );
            } else {
                $query['fields'][] = "MATCH(data) AGAINST('{$query['term']}' IN BOOLEAN MODE) AS score";
            }

            if (empty($query['order'])) {
                $query['order'] = "score DESC";
            }
            return $query;
        } else if ($state == 'after') {
            if (empty($results)) {
                return false;
            }

            if (in_array('data', $query['fields'])) {
                $mapping = $this->getHashedMapping();
                if (empty($query['keep'])) {
                    foreach ($results as &$result) {
                        $data = json_decode($result[$this->alias]['data']);
                        $result[$this->alias]['data'] = array();

                        foreach ($mapping as $field => $hash) {
                            if (!isset($data->$hash)) continue;
                            $result[$this->alias]['data'][$field] = $data->$hash;
                        }
                    }
                } else {
                    foreach ($results as &$result) {
                        $data = json_decode($result[$this->alias]['data']);
                        $result[$this->alias]['data'] = array();

                        foreach ($mapping as $field => $hash) {
                            if (!in_array($field, $query['keep'])) continue;
                            if (!isset($data->$hash)) continue;
                            $result[$this->alias]['data'][$field] = $data->$hash;
                        }
                    }
                }
            }

            if (empty($query['reindex'])) {
                return $results;
            }

            foreach ($results as &$result) {
                $result = $result[$this->alias];
            }
            return $results;
        }
    }

    function _findTypes($state, $query, $results = array()) {
        if ($state == 'before') {
            $query['fields'] = array(
                "DISTINCT({$this->alias}.model)",
                "DISTINCT({$this->alias}.model)"
            );
            return $query;
        } else if ($state == 'after') {
            $types = array();
            $results = Set::extract("/{$this->alias}/model", $results);

            foreach ($results as $type) {
                $types[$type] = Inflector::humanize(Inflector::tableize($type));
            }

            if (!empty($types)) {
                Cache::write('app_search_index_types', $types);
            }
            return $types;
        }
    }

    function replace($v) {
        return str_replace(array(' +-', ' +~', ' ++', ' +'), array('-', '~', '+', '+'), " +{$v}");
    }

    function getSearch($query = null) {
        return $this->find('search', array(
            'term'      => $query,
            'like'      => true,
            'reindex'   => true,
            'fields'    => array(
                '`foreign_key` as `id`',
                'name',
                'summary',
                'data'
            ),
            'keep'      => array(
                'Package.id',
                'Maintainer.name',
                'Package.repository_url',
            )
        ));
    }

}