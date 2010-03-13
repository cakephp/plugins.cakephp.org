<?php
class AppHelper extends Helper {
	var $view = null;

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