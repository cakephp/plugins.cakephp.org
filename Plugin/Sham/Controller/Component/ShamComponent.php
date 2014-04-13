<?php

App::uses('Router', 'Routing');

/**
 * ShamComponent class
 *
 * combines code from various locations (Symfony core, mi_seo)
 *
 * @uses          Object
 * @package       sham
 * @subpackage    sham.controllers.components
 */
class ShamComponent extends Component {

/**
 * Other components used by the Seo component
 *
 * @var array
 * @access public
 */
	public $components = array('RequestHandler');

/**
 * Meta headers for the current request
 *
 * @var array
 */
	public $meta = array();

/**
 * Configuration settings for the component
 *
 * @var array
 */
	public $settings = array(
		'autoRun' => true,
		'encoding' => 'UTF-8',
		'fallback' => '_seoFallback',
		'maxArgs' => null,
		'sortNamedParams' => true,
		'skipExtensions' => array('html'),
	);

/**
 * Request object
 *
 * @var CakeRequest
 */
	public $request;

/**
 * Response object
 *
 * @var CakeResponse
 */
	public $response;

/**
 * Method list for bound controller
 *
 * @var array
 */
	protected $_methods = array();

/**
 * Constructor.
 *
 * @param ComponentCollection $collection
 * @param array $settings
 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		$this->settings['encoding'] = Configure::read('App.encoding');
		$settings = array_merge($this->settings, $settings);
		parent::__construct($collection, $settings);
	}

/**
 * Initialize component
 *
 * @param object $controller Instantiating controller
 * @access public
 */
	public function initialize(Controller $controller) {
		$this->Controller = $controller;

		$this->request = $controller->request;
		$this->response = $controller->response;
		$this->_methods = $controller->methods;

		if ($this->settings['autoRun'] && $controller->name != 'CakeError') {
			$this->check($this->settings['maxArgs']);
		}
	}

/**
 * Sorts the redirect url if necessary
 *
 * @param mixed $Controller
 * @param mixed $url
 * @param mixed $status
 * @param mixed $exit
 * @return void
 * @access public
 */
	public function beforeRedirect(Controller $controller, $url, $status = null, $exit = true) {
		if ($this->settings['sortNamedParams']) {
			return $this->sortUrl($url);
		}
	}

/**
 * Sets seo headers for the view
 *
 * @access public
 */
	public function beforeRender(Controller $controller) {
		if (!isset($this->Controller)) {
			return;
		}

		if (method_exists($this->Controller, '_seo' . ucfirst($this->Controller->params['action']))) {
			$this->Controller->{'_seo' . ucfirst($this->Controller->params['action'])}();
		} elseif (method_exists($this->Controller, $this->settings['fallback'])) {
			$this->Controller->{$this->settings['fallback']}();
		}

		if (method_exists($this->Controller, '_seoAfterSham')) {
			$this->Controller->{'_seoAfterSham'}();
		}

		$this->setMeta('charset', Configure::read('App.encoding'));
		$this->Controller->set('_meta', $this->meta);
	}

/**
 * Loads the metadata record into the view
 *
 * @return void
 */
	public function loadBySlug($slug = null) {
		if (!$slug) {
			$slug = $this->Controller->here;
		}

		if ($slug === '/') {
			$slug = 'root_path';
		} elseif ($slug[0] == '/') {
			$slug = substr($slug, 1);
		}

		$this->Controller->loadModel('Sham.Seo');
		$seo = $this->Controller->Seo->retrieveBySlug($slug, array(
			'seo_only' => true,
			'uri' => $this->Controller->here
		));

		if (!$seo) {
			return false;
		}

		$this->Controller->set('h2_for_layout', $seo['h2_for_layout']);

		$this->setMeta('title', $seo['title_for_layout']);
		unset($seo['title_for_layout'], $seo['h2_for_layout']);

		foreach ($seo as $header => $value) {
			if (!strlen($value)) {
				continue;
			}

			if ($header == 'canonical') {
				$this->setMeta('canonical', $value, array('escape' => false));
			} else {
				$this->setMeta($header, $value);
			}
		}

		return true;
	}

/**
 * Retrieves all meta headers
 *
 * @return array List of meta headers
 */
	public function getMetas() {
		return $this->meta;
	}

/**
 * Retrieves a meta header for the current web response
 *
 * @return void
 **/
	public function getMeta($key) {
		return isset($this->meta[$key]) ? $this->meta[$key] : null;
	}

/**
 * Sets a meta header
 *
 * @param string  $key      Name of the header
 * @param string  $value    Meta header value (if null, remove the meta)
 * @param mixed   $options  If boolean, the value of replace, else an array of options
 *                          bool    $escape   true for escaping the header
 *                          string  $encoding encoding accepted by htmlspecialchars
 *                          bool    $replace  true if it's replaceable
 * @param bool True if meta header is overridden, false otherwise
 */
	public function setMeta($key, $value, $options = array()) {
		if (is_bool($options)) {
			$options = array('replace' => $options);
		}

		$options = array_merge(array(
			'escape' => true,
			'encoding' => $this->settings['encoding'],
			'replace' => false,
		), (array)$options);
		$key = strtolower($key);

		if (is_null($value)) {
			unset($this->meta[$key]);
			return;
		}

		if ($options['escape']) {
			$value = htmlspecialchars($value, ENT_QUOTES, $options['encoding']);
		}

		$current = isset($this->meta[$key]) ? $this->meta[$key] : null;
		if ($options['replace'] || !$current) {
			$this->meta[$key] = $value;
			return true;
		}
		return false;
	}

/**
 * Verify that the current url matches the (first) Router definition and prevent duplicate urls existing to point at
 * the same content.
 * Disabled for requestAction and POST requests
 *
 * @return void
 * @access public
 */
	public function check($maxArgs = null) {
		if (isset($this->Controller->params['requested']) || $this->RequestHandler->isAjax() || $this->Controller->data) {
			return;
		}

		$here = str_replace(' ', '+', '/' . trim(str_replace($this->Controller->webroot, '/', $this->Controller->here), '/'));
		if ($maxArgs !== null) {
			if ($maxArgs) {
				list($url) = array_chunk($this->Controller->params['pass'], $maxArgs);
			} else {
				$url = array();
			}
			$url = $url + $this->Controller->params['named'];
		} else {
			$url = $this->Controller->passedArgs;
		}

		$numeric = array();
		foreach ($url as $key => $value) {
			if (is_int($key) && (is_int($value) || is_string($value))) {
				$numeric[$value] = $key;
			}
		}

		$skip = array('bare', 'form', 'isAjax', 'pass', 'url', 'data', 'named');
		foreach ($this->Controller->params as $key => $value) {
			if (in_array($key, $skip)) {
				continue;
			}

			if (!isset($url[$key])) {
				$url[$key] = $value;
				if (in_array($value, array_keys($numeric), true)) {
					unset($url[$numeric[$value]]);
				}
			}
		}

		if ($this->_addExt()) {
			$url['ext'] = $this->request->params['ext'];
		}

		if ($this->settings['sortNamedParams']) {
			$url = $this->sortUrl($url);
		}

		$normalized = str_replace(' ', '+', Router::normalize($url));
		if ($normalized !== $here) {
			if (Configure::read('debug')) {
				$this->Controller->Session->setFlash('SEOComponent: Redirecting from "' . $here . '" to "' . $normalized . '"');
			}
			$normalized = str_replace('+', '%20', Router::normalize($url));
			return $this->Controller->redirect($normalized, 301);
		}
	}

/**
 * sortUrl method
 *
 * Sort the named parameters in the url alphabetically. prevents two urls each containing the same named parameters in
 * different orders ('.../page:2/sort:id', '.../sort:id/page:2') from being considered different
 * Also called statically by AppHelper::url
 *
 * @param mixed $url
 * @return mixed $url
 * @access public
 */
	public function sortUrl($url = null) {
		if (is_string($url)) {
			return $url;
		}

		if ($url) {
			$named = array();
			$skip = array('bare', 'action', 'controller', 'plugin', 'ext', '?', '#', 'prefix', Configure::read('Routing.admin'));
			$keys = array_values(array_diff(array_keys($url), $skip));
			foreach ($keys as $key) {
				if (!is_numeric($key)) {
					$named[$key] = $url[$key];
				}
			}
		} elseif (isset($this->Controller)) {
			$url = $this->Controller->passedArgs;
			$named = $this->Controller->params['named'];
		} elseif (isset($this->parms['pass'])) {
			$url = $this->Controller->params['pass'];
			$named = $this->Controller->params['named'];
		} else {
			return $url;
		}

		if (!$named) {
			return $url;
		}

		ksort($named);
		return am($named, $url);
	}

	protected function _addExt() {
		if (empty($this->request->params['ext'])) {
			return false;
		}

		if (!empty($this->request->params['ext'])) {
			foreach ($this->settings['skipExtensions'] as $ext) {
				if ($this->request->params['ext'] == $ext) {
					return false;
				}
			}
		}

		return true;
	}
}
