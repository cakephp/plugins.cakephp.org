<?php
App::uses('BaseEmail', 'Job');

class SuggestPackageJob extends BaseEmail {

	public function __construct($username, $repository) {
		parent::__construct(null, compact('username', 'repository'));
	}

	public function build() {
		$vars = $this->getVars();

		parent::build();

		$this->_email = Configure::read('Email.username');
		$this->updateVars(array(
			'subject' => sprintf("New Package: %s/%s", $vars['username'], $vars['repository']),
			'template' => 'suggest_package',
			'variables' => array(
				'username' => $vars['username'],
				'repository' => $vars['repository']
			),
		));
	}

}