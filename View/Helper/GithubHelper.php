<?php
class GithubHelper extends AppHelper {

	public $helpers = array('Sanction.Clearance', 'Html');

	public function existing($username = null, $name = null) {
		if (!$username) {
			return null;
		}

		$name = $this->name($name);

		return $this->Html->link("{$username} {$name}", array(
			'controller' => 'github',
			'action' => 'view',
			$username
		));
	}

	public function name($name = null) {
		return (!empty($name) && $name != ' ' && $name != '') ? "({$name})" : false;
	}

	public function package($name, $maintainer) {
		return $this->Html->link($name, array(
			'controller' => 'packages',
			'action' => 'view',
			$maintainer,
			$name,
		));
	}

}
