<?php
/**
 * Application Model class
 *
 * Add your application-wide methods to the class, your models will inherit them.
 *
 * @package       app
 */
if (!class_exists('LazyModel')) {
	App::import('Lib', 'LazyModel.LazyModel');
}
class AppModel extends LazyModel {

/**
 * List of behaviors to load when the model object is initialized. Settings can be
 * passed to behaviors by using the behavior name as index. Eg:
 *
 * var $actsAs = array('Translate', 'MyBehavior' => array('setting1' => 'value1'))
 *
 * @var array
 * @access public
 * @link http://book.cakephp.org/view/1072/Using-Behaviors
 */
	public $actsAs = array(
		'CakeDjjob.CakeDjjob',
		'Containable',
	);

/**
 * Number of associations to recurse through during find calls. Fetches only
 * the first level by default.
 *
 * @var integer
 * @access public
 * @link http://book.cakephp.org/view/1057/Model-Attributes#recursive-1063
 */
	public $recursive = -1;

/**
 * Custom Model::paginateCount() method to support custom model find pagination
 *
 * @param array $conditions
 * @param int $recursive
 * @param array $extra
 * @return array
 */
    function paginateCount($conditions = array(), $recursive = 0, $extra = array()) {
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
 * @access protected
 * @see Model::find()
 */
    function _findCount($state, $query, $results = array()) {
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
    function get($method) {
        $params = func_get_args();
        array_shift($params);
        $method ='_get' . ucfirst($method);

        if (!method_exists($this, $method)) {
            return false;
        }

        return call_user_func_array(array(&$this, $method), $params);
    }

}