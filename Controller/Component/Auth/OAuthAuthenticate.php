<?php

App::uses('BaseAuthenticate', 'Controller/Component/Auth');
App::uses('HttpSocketOauth', 'Network/Http');
App::uses('Router', 'Routing');

class OAuthAuthenticate extends BaseAuthenticate {

	public $settings = array(
		'authorize_uri' => array(
			'controller' => 'users',
			'action' => 'oauth_login'
		),
		'error_uri' => array(
			'controller' => 'users',
			'action' => 'index',
		),
		'paths' => array(
			'access' => array(
				'path' => '/oauth/access_token',
				'host' => 'example.com',
				'schema' => 'https',
				'port' => 443,
			),
			'auth' => array(
				'path' => '/oauth/user',
				'host' => 'example.com',
				'schema' => 'https',
				'port' => 443,
			),
		),
		'consumer_key' => null,
		'consumer_secret' => null,
		'onLogin' => null,
		'sessionKey' => 'OAuth'
	);

/**
 * Constructor
 *
 * @param ComponentCollection $collection The Component collection used on this request.
 * @param array $settings Array of settings to use.
 */
	public function __construct(ComponentCollection $collection, $settings) {
		$this->_Collection = $collection;
		$this->settings = Set::merge($this->settings, $settings);

		$settings = Configure::read('OAuthAuthenticate');
		if ($settings) {
			$this->settings = Set::merge($this->settings, $settings);
		}
	}

/**
 * Authenticate a user based on the request information.
 *
 * @param CakeRequest $request Request to get authentication information from.
 * @param CakeResponse $response A response object that can have headers added.
 * @return mixed Either false on failure, or an array of user data on success.
 */
	public function authenticate(CakeRequest $request, CakeResponse $response) {
		$user = $this->getUser($request);
		if ($user) {
			return $user;
		}

		if (isset($request->query['code']) && !empty($request->query['code'])) {
			$response = $this->_authorizeToken($request);
			if ($response) {
				return $this->_authUser($response);
			}
		}

		$this->_Collection->getController()->redirect(str_replace(
			array('{consumer_key}', '{authorize_uri}'),
			array($this->settings['consumer_key'], Router::url($this->settings['authorize_uri'], true)),
			$this->settings['remote_authorize_uri']
		));
	}

/**
 * Retrieves an authorization token
 *
 * @param CakeRequest $request Request to get authentication information from.
 * @todo  Ensure access_token is in $response
 * @return array
 */
	protected function _authorizeToken(CakeRequest $request) {
		$request = array(
			'uri' => array(
				'host' => $this->settings['paths']['access']['host'],
				'schema' => $this->settings['paths']['access']['schema'],
				'path' => $this->settings['paths']['access']['path'],
				'port' => $this->settings['paths']['access']['port'],
			),
			'body' => array(
				'code' => $request->query['code'],
				'client_id' => $this->settings['consumer_key'],
				'client_secret' => $this->settings['consumer_secret'],
			),
			'method' => 'POST',
			'auth' => array(
				'method' => 'OAuth',
				'oauth_consumer_key' => $this->settings['consumer_key'],
				'oauth_consumer_secret' => $this->settings['consumer_secret'],
			),
		);

		$this->Http = new HttpSocketOauth();
		$response = $this->Http->request($request);
		parse_str($response, $response);

		if (isset($response['error'])) {
			$this->_Collection->Auth->Session->setFlash("Authentication error: " . $response['error']);
			if ($this->settings['error_uri']) {
				$this->_Collection->getController()->redirect($this->settings['error_uri']);
			}
			return false;
		}

		return $response;
	}

/**
 * Retrieves the user data from the OAuth service
 *
 * @param array $tokenResponse An array of token information
 * @return array
 */
	protected function _authUser($tokenResponse) {
		$request = array(
			'uri' => array(
				'host' => $this->settings['paths']['auth']['host'],
				'schema' => $this->settings['paths']['auth']['schema'],
				'path' => str_replace('{access_token}', $tokenResponse['access_token'], $this->settings['paths']['auth']['path']),
				'port' => 443,
			),
			'method' => 'GET',
			'auth' => array(
				'method' => 'OAuth',
				'access_token' => $tokenResponse['access_token'],
				'oauth_consumer_key' => $this->settings['consumer_key'],
				'oauth_consumer_secret' => $this->settings['consumer_secret'],
			),
		);

		$this->Http = new HttpSocketOauth();
		$response = $this->Http->request($request);

		$data = json_decode($response, true);
		if (!$data || !isset($data['id'])) {
			return false;
		}

		$data['oauth_id'] = $data['id'];
		$data['access_token'] = $tokenResponse['access_token'];
		$data['login_type'] = $this->settings['service'];
		unset($data['id'], $data['plan']);

		$userModel = $this->settings['userModel'];
		list($plugin, $model) = pluginSplit($userModel);
		$user = array($model => $data);

		$this->_Collection->Auth->Session->write($this->settings['sessionKey'], $user);
		if (is_callable($this->settings['onLogin'])) {
			return call_user_func_array($this->settings['onLogin'], $user);
		}
		return $user[$model];
	}

/**
 * Get a user based on information in the request. Primarily used by stateless authentication
 * systems like basic and digest auth.
 *
 * @param CakeRequest $request Request object.
 * @return mixed Either false or an array of user information
 */
	public function getUser(CakeRequest $request) {
		$user = $this->_Collection->Auth->Session->read($this->settings['sessionKey']);
		if (!$user) {
			return false;
		}

		$userModel = $this->settings['userModel'];
		list($plugin, $model) = pluginSplit($userModel);
		return $user[$model];
	}

/**
 * Allows you to hook into AuthComponent::logout(),
 * and implement specialized logout behavior.
 *
 * All attached authentication objects will have this method
 * called when a user logs out.
 *
 * @param array $user The user about to be logged out.
 * @return void
 */
	public function logout($user) {
		$this->_Collection->Auth->Session->delete($this->settings['sessionKey']);
	}

}
