<?php
/**
 * ShamHelper helper library.
 *
 * Helps output seo meta headers on a page
 *
 * @package       sham
 * @subpackage    sham.view.helpers
 */
class ShamHelper extends AppHelper {

/**
 * Included helpers.
 *
 * @var array
 */
	public $helpers = array('Html');

/**
 * Meta headers for the response
 *
 * @var array
 */
	public $meta = array();

	public $canonical = null;

/**
 * Class Constructor
 *
 * @param array $options
 */
	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);

		if (isset($this->_View->viewVars['_meta']['canonical'])) {
			$this->canonical = $this->_View->viewVars['_meta']['canonical'];
		} elseif (isset($this->_View->viewVars['_canonical'])) {
			$this->canonical = $this->_View->viewVars['_canonical'];
		} else {
			$this->canonical = $this->_View->here;
		}

		if (isset($this->_View->viewVars['_meta'])) {
			$this->meta = (array)$this->_View->viewVars['_meta'];
		}

		$this->meta['canonical'] = $this->canonical;
		$this->meta = array_merge((array)$settings, $this->meta);
	}

/**
 * Outputs a canonical tag to the page
 *
 * @param mixed $url canonical url override
 * @return string
 **/
	public function canonical($url = null) {
		if (!$url) {
			$url = $this->canonical;
		}

		if (is_array($url)) {
			$url = Router::url($url, true);
		}

		if (!preg_match('/^(https:\/\/|http:\/\/)/', $url)) {
			$url = Router::url($url, true);
		}

		return $this->Html->meta('canonical', $url, array('rel' => 'canonical', 'type' => null, 'title' => null));
	}

/**
 * Outputs a meta header or series of meta headers
 *
 * @param string $header Specific meta header to output
 * @param array $options
 * @return string
 */
	public function out($header = null, $options = array()) {
		$options = array_merge(array(
			'implode' => '',
			'skip' => array(),
		), (array)$options);

		if (!is_array($options['skip'])) {
			$options['skip'] = (array)$options['skip'];
		}

		if ($header) {
			if (!isset($this->meta[$header])) {
				return;
			}

			if ($header == 'charset') {
				return $this->Html->tag('meta', null, array(
					'charset' => strtolower($this->meta[$header])
				));
			}

			if ($header == 'title') {
				return $this->Html->tag('title', $this->meta[$header]);
			}

			if ($header == 'canonical') {
				return $this->canonical($this->meta[$header]);
			}

			return $this->Html->tag('meta', null, array(
				'name' => $header, 'content' => $this->meta[$header]
			));
		}

		$results = array();
		$meta = $this->meta;

		if (isset($this->meta['charset']) && !in_array('charset', $options['skip'])) {
			$results[] = $this->out('charset', $options);
			$options['skip'][] = 'charset';
		}

		if (isset($this->meta['title']) && !in_array('title', $options['skip'])) {
			$results[] = $this->out('title', $options);
			$options['skip'][] = 'title';
		}

		if (isset($this->meta['canonical']) && !in_array('canonical', $options['skip'])) {
			$results[] = $this->out('canonical', $options);
			$options['skip'][] = 'canonical';
		}

		foreach ($this->meta as $header => $value) {
			if (in_array($header, $options['skip'])) {
				continue;
			}

			$results[] = $this->out($header, $options);
		}

		return implode($options['implode'], $results);
	}

}
