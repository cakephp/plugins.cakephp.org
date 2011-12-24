<?php
class ResourceHelper extends AppHelper {

	public $helpers = array('Form', 'Html', 'Text', 'Time');

	public function package($name, $maintainer) {
		return $this->Html->link($name,
			array('plugin' => null, 'controller' => 'packages', 'action' => 'view', $maintainer, $name),
			array('class' => 'package_name')
		);
	}

	public function github_url($maintainer, $name) {
		$link = "https://github.com/{$maintainer}/{$name}";
		return $this->Html->tag('span', $this->Html->link($link, $link, array(
			'target' => '_blank'
		)), array('class' => 'mobile-block'));
	}

	public function clone_url($maintainer, $name) {
		return $this->Form->input('clone', array(
			'class' => 'mobile-block',
			'div' => false,
			'label' => false,
			'value' => "git://github.com/{$maintainer}/{$name}.git"
		));
	}

	public function __n($value, $singular, $plural) {
		return $this->Html->tag('div',
			$value . ' ' . __n($singular, $plural, $value)
		);
	}

	public function maintainer($username, $name = '') {
		$name = trim($name);
		return $this->Html->link(!empty($name) ? $name : $username,
			array('plugin' => null, 'controller' => 'maintainers', 'action' => 'view', $username),
			array('class' => 'maintainer_name')
		);
	}

	public function maintainer_name($username, $name) {
		if (strlen($name)) {
			return sprintf("%s (%s)", $username, $name);
		}
		return $username;
	}

	public function gravatar($username, $gravatar_id = null) {
		if (!$gravatar_id) {
			return '';
		}

		$format = 'https://secure.gravatar.com/avatar/';
		return $this->Html->image(sprintf($format, $gravatar_id), array(
			'alt' => sprintf('Gravatar for %s', $username),
			'class' => 'gravatar',
			'width' => 50
		));
	}

	public function description($text) {
		if (!strlen(trim($text))) {
			return;
		}

		$hash = sha1($text);
		if (($record = Cache::read('package.description.' . $hash)) !== false) {
			return $record;
		}

		$text = ereg_replace(
			"[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]",
			"<a href=\"\\0\">link</a>",
			$text
		);
		$text = $this->Text->truncate($text, 100, array('html' => true));
		Cache::write('package.description.' . $hash, $text);
		return $this->Html->tag('p', $text);
	}

	public function license($tags = null) {
		return $this->Html->tag('p', 'MIT License');
	}

}