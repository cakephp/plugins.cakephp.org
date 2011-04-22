<?php
class ApiController extends AppController {

/**
 * Use no layout by default for any requests
 *
 * @var boolean
 */
    var $layout = false;

/**
 * Array containing the names of components this controller uses.
 *
 * @var array
 */
	var $components = array('Security');

/**
 * Use no models by default
 *
 * @var string
 */
    var $uses = array();

/**
 * Valid http status codes
 *
 * @var string
 */
    var $status = array(
        200 => array('status' => 200, 'error' => null),
        400 => array('status' => 400, 'error' => 'bad request'),
        404 => array('status' => 404, 'error' => 'not found'),
    );

/**
 * Blackhole all requests by default
 *
 * @var boolean
 */
    var $blackHole = true;

    function one_search($query = null) {
        if (!$query) {
            return $this->setAction('response', $this->status[400]);
        }

        $this->loadModel('ApiSearchIndex');
        $results = $this->ApiSearchIndex->getSearch($query);
        if (!$results) $results = array();

        $results = array_merge(array(
            'count'   => count($results),
            'results' => $results
        ), $this->status[200]);

        return $this->setAction('response', $results);
    }

    function one_install($query = null) {
        if (empty($this->params['url']['package'])) {
            return $this->setAction('response', $this->status[400]);
        }

        $this->loadModel('ApiPackage');
        $results = $this->ApiPackage->find('install', array(
            'request' => $this->params['url']
        ));

        if (!$results) $results = array();

        $results = array_merge($this->status[200], array(
            'count'   => count($results),
            'results' => $results
        ));

        return $this->setAction('response', $results);
    }

    function response($results = array()) {
        if ($this->blackHole) {
            $this->Security->blackHole($this, $this->status[400]['error']);
        }

        Configure::write('debug', 0);
        $this->set('results', $results);
    }

    function setAction($action) {
        $this->blackHole = false;

        return call_user_func_array('parent::setAction', func_get_args());
    }

}