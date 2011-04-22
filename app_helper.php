<?php
App::import('Vendor', 'UrlCache.url_cache_app_helper');
class AppHelper extends UrlCacheAppHelper {
	var $view = null;

	function for_layout($content, $name) {
		ob_start();
		if (!$this->view) {
			$this->view = ClassRegistry::getObject('view');
		}
		echo $content;
		$this->view->set("{$name}_for_layout", ob_get_clean());
	}

	function h2($contents, $alternate = null) {
		$alt = trim($contents);
		if ((strlen($alt) === 0) && isset($alternate)) {
			$contents = $alternate;
		}

		$this->for_layout("<h2>{$contents}</h2> |", 'title');
		$this->for_layout("<h2>{$contents}</h2>", 'h2');
	}
}