<?php
class ResourceHelper extends AppHelper {

	public $helpers = array('Html', 'Text');

	public function package($name, $maintainer) {
		return $this->Html->link($name,
			array('plugin' => null, 'controller' => 'packages', 'action' => 'view', $maintainer, $name),
			array('class' => 'package_name')
		);
	}

	public function maintainer($name = null, $username = null) {
		$name = trim($name);
		$name = (!empty($name)) ? $name : $username;
		return $this->Html->link($name,
			array('plugin' => null, 'controller' => 'maintainers', 'action' => 'view', $username),
			array('class' => 'maintainer_name')
		);
	}

	public function clone_url($maintainer, $name) {
		return "git://github.com/{$maintainer}/{$name}.git";
	}

	public function repository($maintainer, $name) {
		return $this->Html->link("http://github.com/{$maintainer}/{$name}",
			"http://github.com/{$maintainer}/{$name}", array('target' => '_blank')
		);
	}

	public function description($text) {
		if (!strlen(trim($text))) {
			return;
		}

		$hash = sha1($text);
		if (($record = Cache::read('package.description.' . $hash)) !== false) {
			return $record;
		}

		$text = ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]","<a href=\"\\0\">link</a>", $text);
		$text = $this->Text->truncate($text, 100, array('html' => true));
		Cache::write('package.description.' . $hash, $text);
		return $text;
	}

}