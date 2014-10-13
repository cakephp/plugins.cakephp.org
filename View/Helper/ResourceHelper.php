<?php
class ResourceHelper extends AppHelper {

	public $helpers = array('Form', 'Html', 'Text', 'Time');

	public function packageLink($name, $packageId, $slug) {
		return $this->Html->link($name, array(
			'plugin' => null,
			'controller' => 'packages',
			'action' => 'view',
			'id' => $packageId, 'slug' => $slug
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

	public function githubUrl($maintainer, $package, $name = null) {
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

	public function cloneUrl($maintainer, $name) {
		return $this->Form->input('clone', array(
			'class' => 'form-control',
			'div' => false,
			'label' => false,
			'value' => "git://github.com/{$maintainer}/{$name}.git"
		));
	}

	public function gravatar($username, $avatarUrl, $gravatarId = null) {
		if (empty($avatarUrl) && empty($gravatarId)) {
			return '';
		}

		if (empty($avatarUrl)) {
			$avatarUrl = sprintf('https://secure.gravatar.com/avatar/%s', $gravatarId);
		}

		return $this->Html->image($avatarUrl, array(
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
		foreach (Package::$validShownOrders as $sort => $name) {
			if ($sort == $sortField) {
				$output[] = $this->Html->link($name, array('?' => array_merge(
					(array)$this->_View->request->query,
					compact('sort', 'direction', 'order')
				)), array('class' => 'active ' . $direction));
			} else {
				$output[] = $this->Html->link($name, array('?' => array_merge(
					(array)$this->_View->request->query,
					array('sort' => $sort, 'direction' => 'desc', 'order' => $order)
				)));
			}
		}

		return implode(' ', $output);
	}

}
