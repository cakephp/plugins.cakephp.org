<?php
class PackageExistsJob extends CakeJob {

	public $package;

	public function __construct($package) {
		$this->package = $package;
	}

	public function perform() {
		$this->loadModel('Package');
		$exists = $this->Package->findOnGithub($this->package);

		if ($exists) {
			$this->out(sprintf(__('* Record %s exists'), $this->package['Package']['id']));
			return;
		}

		if ($this->Package->softDelete($this->package['Package']['id'], false)) {
			$this->out(sprintf(__('* Record %s deleted'), $this->package['Package']['id']));
		} else {
			$this->out(sprintf(__('* Unable to delete record')));
		}
	}

}