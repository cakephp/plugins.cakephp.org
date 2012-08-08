<?php
App::uses('DeferredEmail', 'Console/Command');

class SuggestPackageJob extends DeferredEmail {

	public function build() {
		$vars = $this->getVars();
		parent::build();

		$this->_email = Configure::read('Email.username');
		$this->updateVars(array(
			'subject' => sprintf("New Package: %s/%s", $vars['username'], $vars['repository']),
			'template' => 'suggest_package',
			'variables' => array(
				'ipaddress' => $vars['ipaddress'],
				'username' => $vars['username'],
				'repository' => $vars['repository']
			),
		));
	}

}