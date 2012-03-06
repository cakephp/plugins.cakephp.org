<?php
class ApiPackage extends AppModel {

/**
 * Name of the model.
 *
 * @var string
 * @link http://book.cakephp.org/view/1057/Model-Attributes#name-1068
 */
	public $name = 'ApiPackage';

/**
 * Detailed list of belongsTo associations.
 *
 * @var array
 * @link http://book.cakephp.org/view/1042/belongsTo
 */
	public $belongsTo = array('Maintainer');

/**
 * Custom database table name, or null/false if no table association is desired.
 *
 * @var string
 * @link http://book.cakephp.org/view/1057/Model-Attributes#useTable-1059
 */
	public $useTable = 'packages';

/**
 * Override the constructor to provide custom model finds
 *
 * @param mixed $id Set this ID for this model on startup, can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->findMethods['install'] = true;
	}

/**
 * Custom find that attaches may "source" to the returned results
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @return mixed array of results or false if none found
 * @return void
 */
	public function _findInstall($state, $query, $results = array()) {
		if ($state == 'before') {
			if (!empty($query['request']['package'])) {
				$query['conditions'] = array(
					$this->alias . '.name LIKE' => $query['request']['package'],
					$this->alias . '.deleted' => false,
				);
			}

			$query['fields'] = array('name', 'description', 'last_pushed_at');

			$contains = array();

			if (isset($query['request']['maintainer'])) {
				$query['conditions']['Maintainer.username LIKE'] = $query['request']['maintainer'];
				$contains['Maintainer'] = array('username', 'name');
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
				$result['Package'] = $result[$this->alias];
				unset($result[$this->alias], $result['Maintainer']['id']);
			}
			return $results;
		}
	}

}