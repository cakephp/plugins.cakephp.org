<?php
App::import('Vendor', 'UrlCache.url_cache_app_helper');
class AppHelper extends UrlCacheAppHelper {

	var $view = null;

	function for_layout($name, $content) {
		if (!$this->view) {
			$this->view = ClassRegistry::getObject('view');
		}
		$this->view->set("{$name}_for_layout", $content);
	}

	function h2($contents, $alternate = null) {
		$alt = trim($contents);
		if ((strlen($alt) === 0) && isset($alternate)) {
			$contents = $alternate;
		}

		$this->for_layout('title', $contents);
		$this->for_layout('h2', "<h2>{$contents}</h2>");
	}
}