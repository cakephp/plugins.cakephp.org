<?php
App::uses('Model', 'Model');
App::uses('DebugKitDebugger', 'DebugKit.Lib');

/**
 * Application Model class
 *
 * Add your application-wide methods to the class, your models will inherit them.
 *
 * @package	   app
 */
class AppModel extends Model {

/**
 * List of behaviors to load when the model object is initialized. Settings can be
 * passed to behaviors by using the behavior name as index. Eg:
 *
 * var $actsAs = array('Translate', 'MyBehavior' => array('setting1' => 'value1'))
 *
 * @var array
 * @link http://book.cakephp.org/view/1072/Using-Behaviors
 */
	public $actsAs = array(
		'Containable',
	);

/**
 * Number of associations to recurse through during find calls. Fetches only
 * the first level by default.
 *
 * @var integer
 * @link http://book.cakephp.org/view/1057/Model-Attributes#recursive-1063
 */
	public $recursive = -1;

/**
 * Available jobs
 *
 * @var array
 * @access protected
 */
	protected $_jobs = array(
		'NewPackageJob' => array('username', 'name'),
		'SuggestPackageJob' => array('username', 'repository'),
		'UserForgotPasswordJob' => array('user_id', 'ip_address'),
		'UserVerificationEmailJob' => array('user_id'),
	);

/**
 * Return all the available jobs
 */
	public function getJobs() {
		return $this->_jobs;
	}

/**
 * Enqueues a job in resque
 *
 * @return void
 * @author
 **/
	public function enqueue($name, $arguments) {
		array_unshift($arguments, 'work');
		return Resque::enqueue('default', $name, $arguments);
	}

/**
 * Helper for firing jobs
 *
 * @param array $data
 * @return boolean
 */
	public function fireJob($data = array()) {
		if (isset($data['job'])) {
			$new = $data;
			unset($data);
			$data[$this->alias] = $new;
		}
		if (empty($data[$this->alias]['job'])) {
			throw new CakeException(__('Invalid job.'));
			return false;
		}
		foreach ($data[$this->alias] as $key => $val) {
			if ($key == 'job') {
				continue;
			}
			if (strpos($key, '_id') !== false) {
				$model = Inflector::classify(substr($key, 0, strpos($key, '_id')));
				$Model = ClassRegistry::init($model);
				$res = $Model->findById($val);
				$data[$this->alias][$key] = $res[$Model->alias];
			}
		}
		$data = array_values($data[$this->alias]);
		$job = call_user_func_array(array($this, 'load'), $data);
		if (!$job) {
			throw new CakeException(__('Job could not be loaded.'));
		} else {
			if (!$this->enqueue($job)) {
				throw new CakeException(__('Job could not be enqueued.'));
			} else {
				return true;
			}
		}
		return false;
	}

/**
 * Queries the datasource and returns a result set array.
 *
 * @param string $type Type of find operation (all / first / count / neighbors / list / threaded)
 * @param array $query Option fields (conditions / fields / joins / limit / offset / order / page / group / callbacks)
 * @return array Array of records
 * @link http://book.cakephp.org/2.0/en/models/deleting-data.html#deleteall
 */
	public function find($type = 'first', $query = array()) {
		DebugKitDebugger::startTimer($this->name . '::find(' . $type . ')');
		$results = parent::find($type, $query);
		DebugKitDebugger::stopTimer($this->name . '::find(' . $type . ')');
		return $results;
	}

/**
 * Custom Model::paginateCount() method to support custom model find pagination
 *
 * @param array $conditions
 * @param int $recursive
 * @param array $extra
 * @return array
 */
	public function paginateCount($conditions = array(), $recursive = 0, $extra = array()) {
		$parameters = compact('conditions');

		if ($recursive != $this->recursive) {
			$parameters['recursive'] = $recursive;
		}

		if (isset($extra['type'])) {
			$extra['operation'] = 'count';
			return $this->find($extra['type'], array_merge($parameters, $extra));
		} else {
			return $this->find('count', array_merge($parameters, $extra));
		}
	}

/**
 * Removes 'fields' key from count query on custom finds when it is an array,
 * as it will completely break the Model::_findCount() call
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @param array $data
 * @return int The number of records found, or false
 * @see Model::find()
 */
	public function _findCount($state, $query, $results = array()) {
		if ($state == 'before' && isset($query['operation'])) {
			if (!empty($query['fields']) && is_array($query['fields'])) {
				if (!preg_match('/^count/i', $query['fields'][0])) {
					unset($query['fields']);
				}
			}
		}
		return parent::_findCount($state, $query, $results);
	}

/**
 * Wrapper around _get* magic methods
 *
 * @param string $method method name
 * @return results of the _get call
 */
	public function get($method) {
		$params = func_get_args();
		array_shift($params);
		$method ='_get' . ucfirst($method);

		if (!method_exists($this, $method)) {
			return false;
		}

		return call_user_func_array(array(&$this, $method), $params);
	}

}