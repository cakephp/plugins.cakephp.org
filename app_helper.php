<?php
/*
 * App Helper url caching
 * Copyright (c) 2009 Matt Curry
 * www.PseudoCoder.com
 * http://github.com/mcurry/cakephp/tree/master/snippets/app_helper_url
 * http://www.pseudocoder.com/archives/2009/02/27/how-to-save-half-a-second-on-every-cakephp-requestand-maintain-reverse-routing
 *
 * @author      Matt Curry <matt@pseudocoder.com>
 * @license     MIT
 *
 */
class AppHelper extends Helper {
	var $view = null;

	function url($url = null, $full = false) {
		App::import('Vendor', 'mi_cache');
		return MiCache::data('Helper', 'url', $url, $full);
	}

	function h2($contents, $alternate = null) {
		ob_start();
		if ((empty($contents) || $contents == '' || $contents == ' ') && isset($alternate)) {
			$contents = $alternate;
		}
		if (!$this->view) {
			$this->view = ClassRegistry::getObject('view');
		}
		echo "<h2>{$contents}</h2>";
		$this->view->set('title_for_layout', "{$contents} |");
		$this->view->set("h2_for_layout", ob_get_clean());
	}
}
?>