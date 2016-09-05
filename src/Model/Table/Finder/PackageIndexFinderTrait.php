<?php
namespace App\Model\Table\Finder;

use Cake\Log\Log;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\Query;
use InvalidArgumentException;

trait PackageIndexFinderTrait
{
    public $validTypes = array(
        'model', 'controller', 'view',
        'behavior', 'component', 'helper',
        'shell', 'theme', 'datasource',
        'lib', 'test', 'vendor',
        'app', 'config', 'resource',
    );

    /**
     * Returns a valid Package with all related data
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findIndex(Query $query, array $options)
    {
        $options = array_merge([
            'collaborators' => null,
            'contains' => [],
            'contributors' => null,
            'forks' => null,
            'has' => [],
            'open_issues' => null,
            'query' => null,
            'since' => null,
            'version' => null,
            'watchers' => null,
            'with' => [],
        ], $options);
        $options['has'] = array_merge(
            (array)$options['with'],
            (array)$options['contains'],
            (array)$options['has']
        );

        $query->find('package');

        $direction = 'asc';
        if (!empty($options['direction'])) {
            $options['direction'] = strtolower((string)$options['direction']);
            if ($options['direction'] == 'dsc' || $options['direction'] == 'des') {
                $options['direction'] = 'desc';
            }

            if ($options['direction'] != 'asc' && $options['direction'] != 'desc') {
                $options['direction'] = 'desc';
            }
            $direction = $options['direction'];
        }

        $sortField = 'username';
        if (!empty($options['sort'])) {
            $options['sort'] = strtolower($options['sort']);
            if (in_array($options['sort'], Package::$_validOrders)) {
                $sortField = $options['sort'];
            }
        }

        if ($sortField == 'username') {
            $query->order(["Maintainers.{$sortField}" => "{$direction}"]);
        } else {
            $query->order(["{$this->alias()}.{$sortField}" => "{$direction}"]);
        }

        if ($options['collaborators'] !== null) {
            $query->where(["{$this->alias()}.collaborators >=" => (int)$options['collaborators']]);
        }

        if ($options['contributors'] !== null) {
            $query->where(["{$this->alias()}.contributors >=" => (int)$options['contributors']]);
        }

        if ($options['forks'] !== null) {
            $query->where(["{$this->alias()}.forks >=" => (int)$options['forks']]);
        }

        // if (!empty($options['has']) || !empty($options['version'])) {
        //     foreach ($options['has'] as $has) {
        //         $has = inflector::singularize(strtolower($has));
        //         if (in_array($has, $this->validTypes)) {
        //             $query->where([
        //                 'Tag.keyname' => $has,
        //                 'Tag.identifier' => 'contains',
        //             ]);
        //         }
        //     }

        //     if (!empty($options['version'])) {
        //         $options['version'] = str_replace(['.x', '.'], '', $options['version']);
        //         if (array($options['version'], ['12', '13', '2', '3'])) {
        //             $query->where([
        //                 'Tag.keyname LIKE' => $options['version'] . '%',
        //                 'Tag.identifier' => 'version',
        //             ]);
        //         }
        //     }

        //     $query['joins'][] = array(
        //         'alias' => 'Tagged',
        //         'table' => 'tagged',
        //         'type' => 'INNER',
        //         'conditions' => array(
        //             '`Tagged`.`foreign_key` = `' . $this->alias() . '`.`id`',
        //         ),
        //     );
        //     $query['joins'][] = array(
        //         'alias' => 'Tag',
        //         'table' => 'tags',
        //         'type' => 'INNER',
        //         'conditions' => array(
        //             '`Tagged`.`tag_id` = `Tag`.`id`',
        //         ),
        //     );
        // }

        if (!empty($options['category'])) {
            $query->matching('Categories', function ($q) use ($options) {
                return $q->where(['Categories.slug' => $options['category']]);
            });
        }

        if ($options['open_issues'] !== null) {
            $query->where(["{$this->alias()}.open_issues <=" => (int)$options['open_issues']]);
        }

        // if ($options['query'] !== null) {
        //     $query['conditions'][]['OR'] = array(
        //         "{$this->alias()}.name LIKE" => '%' . $options['query'] . '%',
        //         "{$this->alias()}.description LIKE" => '%' . $options['query'] . '%',
        //         "Maintainer.username LIKE" => '%' . $options['query'] . '%',
        //     );
        // }

        if ($options['since'] !== null) {
            $time = date('Y-m-d H:i:s', strtotime($options['since']));
            $query->where(["{$this->alias()}.last_pushed_at >" => $time]);
        }

        if ($options['watchers'] !== null) {
            $query->where(["{$this->alias()}.watchers >=" => (int)$options['watchers']]);
        }

        return $query;
    }
}
