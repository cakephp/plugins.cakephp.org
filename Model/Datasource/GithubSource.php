<?php
App::uses('HttpSocket', 'Network/Http');

class GithubSource extends DataSource {

	public $_schema = array(
		'githubs' => array(),
		'users' => array(),
		'issues' => array(),
		'repositories' => array(),
	);

	public $cacheSources = false;

	public function __construct($config) {
		$config = array_merge(array(
			'host'      => 'github.com',
			'port'      => 443,
			'login'     => null,
			'password'  => null,
			'database'  => 'api/v2/json',
			'cacheKey'  => 'github',
			'duration'  => '+2 days'
		), $config);

		$this->sConfig = array(
			'persistent' => '',
			'encoding' => 'utf-8',
			'header' => array(
				'Content-Type' => 'application/json',
				'encoding' => 'UTF-8',
			),
			'host' => $config['host'],
			'protocol' => '6',
			'port' => $config['port'],
			'timeout' => '30',
			'request' => array(
				'uri' => array(
					'scheme' => 'https',
					'host' => $config['host'],
					'port' => $config['port'],
				),
				'auth' => array(
					'method' => 'Basic',
					'user' => $config['login'],
					'pass' => $config['password'],
				)
			),
		);

		if (!$config['login'] || !$config['password']) {
			unset($this->sConfig['request']['auth']);
			$this->sConfig['port'] = $this->sConfig['request']['url']['port'] = 80;
		}

		$this->connection = new HttpSocket($this->sConfig);
		parent::__construct($config);
	}


/**
 * Returns an empty array
 *
 * @param Object $model Model object to describe
 * @return array empty array
 */
	function describe(&$model) {
		$table = Inflector::tableize($model->alias);
		if (isset($this->_schema[$table])) {
			return $this->_schema[$table];
		}
		return array();
	}

/**
 * Returns an array of sources from github
 *
 * @return array Array of sources from github
 */
	function listSources() {
		return array_keys($this->_schema);
	}

	function read($model, $queryData = array()) {
		if (!$model->findQueryType) {
			return array();
		}
		$remove = array(
			'conditions', 'fields', 'joins', 'limit', 'offset',
			'order', 'page', 'group', 'callbacks'
		);
		$queryData = array_diff_key($queryData, array_combine($remove, $remove));

		$params = null;
		$path = str_replace('_', '/', Inflector::underscore($model->findQueryType));

		if (!empty($queryData)) {
			$params = current($queryData);
		}

		$request  = $this->_readRequest($path, $params);
		$response = $this->_cachedResponse("/{$this->config['database']}/{$request}");
		$response = $this->_formatResponse($response);
		return $response;
	}

	function _readRequest($path, $query) {
		switch ($path) {
			case 'user/show/following' :
				return 'user/show/' . $query . '/following';
			case 'user/watched' :
				return 'repos/watched/' . $query;
			case 'repos/search' : 
				return 'repos/search/' . str_replace(' ', '+', $query);
			case 'repos/show/single' :
				return 'repos/show/' . $query;
			case 'repos/show/collaborators' :
				return 'repos/show/' . $query . '/collaborators';
			case 'repos/show/contributors' :
				return 'repos/show/' . $query . '/contributors';
			case 'repos/show/network' :
				return 'repos/show/' . $query . '/network';
			case 'repos/show/languages' :
				return 'repos/show/' . $query . '/network';
			case 'repos/show/tags' :
				return 'repos/show/' . $query . '/tags';
			case 'commits/show/path' :
				return 'commits/show/' . $query;
			case 'commits/show/sha' :
				return 'commits/show/' . $query . '/sha';
			case 'blob/show/all' :
				return 'blob/all/' . $query;
			case 'blob/show/path' :
				return 'blob/show/' . $query;
		}

		return $path . '/' . $query;
	}

/**
 * Convenience method to fake out Model::_findMethods
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 */
	function query() {
		$queryArgs = func_get_args();
		$method    = $queryArgs[0];
		$arguments = $queryArgs[1];

		if (substr($method, 0, 5) !== '_find') {
			return $arguments;
		}

		if (!is_array($arguments)) {
			return $arguments;
		}

		if ($arguments[0] == 'before') {
			return $arguments[1];
		} elseif ($arguments[0] == 'after') {
			return $arguments[2];
		}

		return $arguments;
	}

/**
 * Retrieves a response from github and caches it for some period of time
 *
 * @param string $request
 * @param string $var
 * @return void
 */
	function _cachedResponse($request, $var = null) {
		$hash = md5(serialize(array($request, $var)));
		$response = array();

		Cache::set(array('duration' => $this->config['duration']));
		if (($response = Cache::read($this->config['cacheKey'] . $hash)) === false) {
			sleep(1);
			$this->connection = new HttpSocket($this->sConfig);
			$response = json_decode($this->connection->get($request . $var), true);

			if (!$response) {
				$this->error = 'response was html page';
				return false;
			}

			if (isset($response['error'])) {
				$this->error = $response['error'];
				return false;
			}

			Cache::set(array('duration' => '+2 days'));
			Cache::write($this->config['cacheKey'] . $hash, $response);
		}
		return $response;
	}

/**
 * Reformat a json_decode'd response to create cakephp-like response
 *
 * Fixes issues like pluralized model names and invalid CakePHP format
 *
 * @param array $data Data to be formatted
 * @return array
 */
	function _formatResponse($data) {
		if (!$data) {
			return array();
		}

		$response = array();
		foreach ($data as $modelName => $keys) {
			$response[Inflector::singularize(ucfirst($modelName))] = $keys;
		}

		if (!Set::numeric(array_keys(current($response)))) {
			return $response;
		}

		$data = array();
		foreach ($response as $modelName => $sets) {
			foreach ($sets as $set) {
				$data[] = array($modelName => $set);
			}
		}

		return $data;
	}

}