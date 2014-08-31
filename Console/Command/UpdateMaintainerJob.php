<?php
App::uses('AppShell', 'Console/Command');
App::uses('PackageData', 'Lib');

class UpdateMaintainerJob extends AppShell {

	public $uses = array('Maintainer');

	public function work() {
		sleep(1);
		$username = $this->args[0];
		$this->Maintainer->updateExistingMaintainer($username);
	}

}
