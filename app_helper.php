<?php
if (!class_exists('UrlCacheAppHelper')) {
	App::import('Vendor', 'UrlCache.url_cache_app_helper');
}
class AppHelper extends UrlCacheAppHelper {

/**
 * Reference to view object
 *
 * @var string
 */
	public $view = null;

/**
 * Helper method to set custom information into a $NAME_for_layout variable
 *
 * @param string $name name of $for_layout variable
 * @param mixed $content Contents to set for_layout
 */
	public function for_layout($name, $content) {
		if (!$this->view) {
			$this->view =& ClassRegistry::getObject('view');
		}
		$this->view->set("{$name}_for_layout", $content);
	}

/**
 * Convenience method to set a title_for_layout and h2_for_layout
 *
 * @param string $contents Contents
 * @param string $alternate Alternative if $contents are empy
 */
	public function h2($contents, $alternate = null) {
		$alt = trim($contents);
		if ((strlen($alt) === 0) && isset($alternate)) {
			$contents = $alternate;
		}

		$this->for_layout('title', $contents);
		$this->for_layout('h2', "<h2>{$contents}</h2>");
	}

}