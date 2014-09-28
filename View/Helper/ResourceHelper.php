<?php
class ResourceHelper extends AppHelper {

	public $helpers = array('Form', 'Html', 'Text', 'Time');

	public function packageLink($name, $package_id, $slug) {
		return $this->Html->link($name, array(
			'plugin' => null,
			'controller' => 'packages',
			'action' => 'view',
			'id' => $package_id, 'slug' => $slug
		), array('title' => $name));
	}

	public function packageUrl($package) {
		return $this->url(array(
			'plugin' => null,
			'controller' => 'packages',
			'action' => 'view',
			'id' => $package['id'], 'slug' => $package['name']
		));
	}

	public function github_url($maintainer, $package, $name = null) {
		$link = "https://github.com/{$maintainer}/{$package}";
		if ($name === null) {
			$name = $link;
		}

		return $this->Html->link($name, $link, array(
			'target' => '_blank',
			'class' => 'external github-external',
			'package-name' => "{$maintainer}-{$package}",
		));
	}

	public function clone_url($maintainer, $name) {
		return $this->Form->input('clone', array(
			'class' => 'form-control',
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

	public function gravatar($username, $avatar_url, $gravatar_id = null) {
		if (empty($avatar_url) && empty($gravatar_id)) {
			return '';
		}

		if (empty($avatar_url)) {
			$avatar_url = sprintf('https://secure.gravatar.com/avatar/%s', $gravatar_id);
		}

		return $this->Html->image($avatar_url, array(
			'alt' => 'Gravatar for ' . $username,
			'class' => 'img-circle'
		));
	}

	public function description($text) {
		$text = trim($text);
		return $this->Html->tag('p', $this->Text->truncate(
			$this->Text->autoLink($text), 100, array('html' => true)
		), array('class' => 'lead'));
	}

	public function sort($order) {
		list($order, $direction) = explode(' ', $order);
		list(, $sortField) = explode('.', $order);

		if ($direction == 'asc') {
			$direction = 'desc';
		} else {
			$direction = 'asc';
		}

		$order = null;

		$output = array();
		foreach (Package::$_validShownOrders as $sort => $name) {
			if ($sort == $sortField) {
				$output[] = $this->Html->link($name, array('?' => array_merge(
					(array) $this->_View->request->query,
					compact('sort', 'direction', 'order')
				)), array('class' => 'active ' . $direction));
			} else {
				$output[] = $this->Html->link($name, array('?' => array_merge(
					(array) $this->_View->request->query,
					array('sort' => $sort, 'direction' => 'desc', 'order' => $order)
				)));
			}
		}

		return implode(' ', $output);
	}

}
