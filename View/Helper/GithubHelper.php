<?php
class GithubHelper extends AppHelper {
	var $helpers = array('Sanction.Clearance', 'Html');

	function existing($username = null, $name = null) {
		if (!$username) return null;

		$name = $this->name($name);

		return $this->Html->link("{$username} {$name}", array(
			'controller' => 'github',
			'action' => 'view',
			$username
		));
	}

	function name($name = null) {
 		return (!empty($name) && $name != ' ' and $name != '') ? "({$name})" : false;
	}

	function package($name, $maintainer) {
		return $this->Html->link($name, array(
			'controller' => 'packages',
			'action' => 'view',
			$maintainer,
			$name,
		));
	}

	function link_to($url = null) {
		if (!$url) return null;

		return $this->Html->link(__('Url'), $url);
	}

}
