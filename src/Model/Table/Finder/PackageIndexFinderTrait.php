<?php
namespace App\Model\Table\Finder;

use Cake\Log\Log;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\Query;
use Cake\Utility\Inflector;
use InvalidArgumentException;

trait PackageIndexFinderTrait
{
    public $validTypes = array(
        'app',
        'auth-storage',
        'authenticate',
        'authorize',
        'behavior',
        'cache-engine',
        'cell',
        'component',
        'composer',
        'config',
        'controller',
        'datasource',
        'elements',
        'entity',
        'fixture',
        'helper',
        'lib',
        'license',
        'locale',
        'log',
        'mail-transport',
        'middleware',
        'model',
        'panel',
        'password-hasher',
        'plugin',
        'readme',
        'resource',
        'route-class',
        'route-filter',
        'shell',
        'table',
        'test',
        'theme',
        'themed',
        'travis',
        'vendor',
        'view',
        'widget',
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
            'contributors' => null,
            'forks' => null,
            'has' => [],
            'keyword' => [],
            'open_issues' => null,
            'query' => null,
            'since' => null,
            'version' => null,
            'watchers' => null,
        ], $options);

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

        // if (!empty($options['version']) || !empty($options['has']) || !empty($options['keyword'])) {
        //     $query->innerJoin(
        //         ['Tagged' => 'tagged'],
        //         ['Tagged.foreign_key = Packages.id']
        //     );
        //     $query->group('Packages.id');
        // }

        if (!empty($options['version'])) {
            $options['version'] = str_replace(['.x', '.'], '', $options['version']);
            if (array($options['version'], ['12', '13', '2', '3'])) {
                $tableName = 'TagsVersion';
                $taggedTableName = 'Tagged' . $tableName;

                $query->innerJoin(
                    [$taggedTableName => 'tagged'],
                    ["${taggedTableName}.foreign_key = Packages.id"]
                );
                $query->innerJoin(
                    [$tableName => 'tags'],
                    [
                        "${tableName}.id = ${taggedTableName}.tag_id",
                        "${tableName}.keyname = '" . $options['version'] . "'",
                        "${tableName}.identifier = 'version'",
                    ]
                );
            }
        }

        if (!empty($options['has'])) {
            foreach ((array)$options['has'] as $has) {
                $has = Inflector::singularize(strtolower($has));
                if ($has == 'travi') {
                    $has = 'travis';
                }
                if (!in_array($has, $this->validTypes)) {
                    continue;
                }
                $tableName = 'Tags' . Inflector::classify(Inflector::underscore($has));
                $taggedTableName = 'Tagged' . $tableName;

                $query->innerJoin(
                    [$taggedTableName => 'tagged'],
                    ["${taggedTableName}.foreign_key = Packages.id"]
                );
                $query->innerJoin(
                    [$tableName => 'tags'],
                    [
                        "${tableName}.id = ${taggedTableName}.tag_id",
                        "${tableName}.keyname = '" . $this->_multibyteKey($has) . "'",
                        "${tableName}.identifier = 'has'",
                    ]
                );
            }
        }

        if (!empty($options['keyword'])) {
            foreach ((array)$options['keyword'] as $keyword) {
                $tableName = 'Tags' . Inflector::classify($keyword);
                $taggedTableName = 'Tagged' . $tableName;

                $query->innerJoin(
                    [$taggedTableName => 'tagged'],
                    ["${taggedTableName}.foreign_key = Packages.id"]
                );
                $query->innerJoin(
                    [$tableName => 'tags'],
                    [
                        "${tableName}.id = ${taggedTableName}.tag_id",
                        "${tableName}.keyname = '" . $this->_multibyteKey($keyword) . "'",
                        "${tableName}.identifier = 'keyword'",
                    ]
                );
            }
        }

        if (!empty($options['category'])) {
            $query->matching('Categories', function ($q) use ($options) {
                return $q->where(['Categories.slug' => $options['category']]);
            });
        }

        if ($options['open_issues'] !== null) {
            $query->where(["{$this->alias()}.open_issues <=" => (int)$options['open_issues']]);
        }

        if ($options['query'] !== null) {
            $_query = sprintf('%%%s%%', $options['query']);
            $query->andWhere(function ($exp, $query) use ($_query) {
                return $query->newExpr()->add([
                    "{$this->alias()}.name LIKE" => $_query,
                    "{$this->alias()}.description LIKE" => $_query,
                    "Maintainers.username LIKE" => $_query,
                ])->tieWith('OR');
            });
        }

        if ($options['since'] !== null) {
            $time = date('Y-m-d H:i:s', strtotime($options['since']));
            $query->where(["{$this->alias()}.last_pushed_at >" => $time]);
        }

        if ($options['watchers'] !== null) {
            $query->where(["{$this->alias()}.watchers >=" => (int)$options['watchers']]);
        }

        return $query;
    }

    /**
     * Creates a multibyte safe unique key
     *
     * @param Model $model Model instance that behavior is attached to
     * @param string $string Tag name string
     * @return string Multibyte safe key string
     */
    protected function _multibyteKey($string = null)
    {
        $str = mb_strtolower($string);
        $str = preg_replace('/\xE3\x80\x80/', ' ', $str);
        $str = str_replace(array('_', '-'), '', $str);
        $str = preg_replace('#[:\#\*"()~$^{}`@+=;,<>!&%\.\]\/\'\\\\|\[]#', "\x20", $str);
        $str = str_replace('?', '', $str);
        $str = trim($str);
        $str = preg_replace('#\x20+#', '', $str);
        return $str;
    }
}
