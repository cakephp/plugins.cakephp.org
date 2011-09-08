<?php
class PackageExistsJob extends CakeJob {

	var $package;

	function __construct($package) {
		$this->package = $package;
	}

	function perform() {
		$this->loadModel('Package');
		$exists = $this->Package->findOnGithub($this->package);

		if ($exists) {
			$this->out(sprintf(__('* Record %s exists', true), $this->package['Package']['id']));
			return;
		}

		if ($this->Package->softDelete($this->package['Package']['id'], false)) {
			$this->out(sprintf(__('* Record %s deleted', true), $this->package['Package']['id']));
		} else {
			$this->out(sprintf(__('* Unable to delete record', true)));
		}
	}

}