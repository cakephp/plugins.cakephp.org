<?php
class ApiController extends AppController {

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

    function beforeRender() {
        parent::beforeFilter();
        Configure::write('debug', 0);
        $this->action = 'response';
        $this->layout = false;
    }

    function one_search($query = null) {
        if (!$query) {
            return $this->set('results', $this->status[400]);
        }

        $this->loadModel('ApiSearchIndex');
        $results = $this->ApiSearchIndex->getSearch($query);
        if (!$results) $results = array();

        $results = array_merge(array(
            'count'   => count($results),
            'results' => $results
        ), $this->status[200]);

        return $this->set(compact('results'));
    }

    function one_install() {
        if (empty($this->params['url']['package'])) {
            return $this->set('results', $this->status[400]);
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
        return $this->set(compact('results'));
    }

}