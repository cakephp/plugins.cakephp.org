<?php
if (!class_exists('BaseEmail')) {
    App::import('Lib', 'BaseEmail');
}
class SuggestPackageJob extends BaseEmail {

	public function __construct($username, $repository) {
		parent::__construct(null, compact('username', 'repository'));
	}

	public function build() {
		parent::build();

		$this->_email = Configure::read('Email.username');
		$this->updateVars(array(
			'subject' => sprintf("New Package: %s/%s", $this->_vars['username'], $this->_vars['repository']),
			'template' => 'suggest_package',
			'variables' => array(
				'username' => $this->_vars['username'],
				'repository' => $this->_vars['repository']
			),
		));
	}

}