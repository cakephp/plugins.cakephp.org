<?php
class PackagesShell extends Shell {

	var $tasks = array('CakeDjjob');
	var $uses = array('Package');

	var $folder = null;

/**
 * Main shell logic.
 *
 * @return void
 * @author John David Anderson
 */
	function main() {
		if (!empty($this->params[0])) {
			$this->command = substr($this->params[0], 0, 1);
		}

		$this->__run();
	}

/**
 * Main application flow control.
 *
 * @return void
 * @author Jose Diaz-Gonzalez <support@savant.be>
 */
	function __run() {

		$validCommands = array('a', 'c', 'e', 'f', 'g', 'r', 't', 'u', 'q');

		while (empty($this->command)) {
			$this->out("Packages Shell");
			$this->hr();
			$this->out("[A]dd missing Attributes");
			$this->out("[C]haracterize Packages");
			$this->out("[E]xistence Check");
			$this->out("[F]ix Repository Urls");
			$this->out("[G]it Clone Repositories");
			$this->out("[R]eset Characteristics");
			$this->out("[Q]uit");
			$temp = $this->in("What command would you like to perform?", $validCommands, 'i');
			if (in_array(strtolower($temp), $validCommands)) {
				$this->command = $temp;
			} else {
				$this->out("Try again.");
			}
		}

		switch ($this->command) {
			case 'a' :
				$this->addMissingAttributes();
				break;
			case 'c' :
				$this->characterize();
				break;
			case 'e' :
				$this->existenceCheck();
				break;
			case 'f' :
				$this->fixRepositoryUrls();
				break;
			case 'g' :
				$this->gitCloneRepositories();
				break;
			case 'r' :
				$this->resetCharacteristics();
				break;
			case 'q' :
				$this->out(__("Exit", true));
				$this->_stop();
				break;
		}
	}

/**
 * Goes through each and every package and update's it's attributes
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 **/
	function addMissingAttributes() {
		$packages = $this->Package->find('all', array(
			'contain' => array('Maintainer' => array('id', 'username')),
			'fields' => array('id', 'name'),
			'order' => array('Package.name ASC')
		));

		$this->Package->Behaviors->detach('Searchable');
		$count = 0;
		foreach ($packages as $package) {
			sleep(1);
			if ($this->Package->updateAttributes($package)) {
				$this->out(sprintf(__('* Updated %s', true), $package['Package']['name']));
				$count++;
			} else {
				$this->out(sprintf(__('* Failed to update %s', true), $package['Package']['name']));
				continue;
			}
		}
		$p_count = count($packages);
		$this->out(sprintf(__('* Updated %s of %s packages', true), $count, $p_count));
	}

/**
 * Disables packages when their existence cannot be verified on github
 *
 * @return void
 * @todo send summary email so packages can be manually verified/removed
 * @author Jose Diaz-Gonzalez
 */
	function existenceCheck() {
		$this->Package->Behaviors->detach('Searchable');
		$packages = $this->Package->find('all', array(
			'contain' => array('Maintainer' => array('id', 'username')),
			'fields' => array('id', 'name'),
			'order' => array('Package.name ASC')
		));

		foreach ($packages as $package) {
			sleep(1);
			$exists = $this->Package->existenceCheck($package);

			if ($exists) {
				$this->out(sprintf(__('* Record %s exists', true), $package['Package']['id']));
			} else {
				$this->out(sprintf(__('* Record %s deleted', true), $package['Package']['id']));
			}
		}
	}

/**
 * Git clones all the repositories from their respective remote
 * locations
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 */
	function gitCloneRepositories() {
		$count = 0;

		$packages = $this->Package->find('list');
		foreach ($packages as $id => $name) {
			$this->out(sprintf(__("* Downloading package %s", true), $name));
			if ($this->Package->setupRepository($id)); {
				$count++;
			}
		}
		$this->out(sprintf(__('* Downloaded %s of %s repositories', true), $count, count($packages)));
	}

/**
 * Fixes clone urls (according to github) for all
 * repositories, regardless of their working status
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 */
	function fixRepositoryUrls() {
		$update_count = 0;

		$this->Package->Behaviors->detach('Searchable');
		$packages = $this->Package->find('all', array(
			'contain' => array('Maintainer' => array('id', 'username')),
			'fields' => array('id', 'maintainer_id', 'name'),
			'order' => array('Package.id ASC')
		));

		foreach ($packages as $package) {
			$this->out(sprintf(
				__('Updating package id %s named %s', true),
				$package['Package']['id'],
				$package['Package']['name']
			));

			if ($this->Package->fixRepositoryUrl($package)) $update_count++;
		}

		$this->out(sprintf(__('* Successfully updated %s out of %s package urls', true), $update_count, count($packages)));
		$this->_stop();
	}

/**
 * Resets all 'contains' attributes for all packages
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 */
	function resetCharacteristics() {
		$characteristics = array_combine(array(
			'contains_model', 'contains_datasource', 'contains_behavior', 'contains_controller',
			'contains_component', 'contains_view', 'contains_helper', 'contains_theme', 'contains_vendor',
			'contains_shell', 'contains_test', 'contains_lib', 'contains_resource', 'contains_config'
		), array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0));

		$this->out(__('Resetting all characteristics', true));
		$this->Package->updateAll($characteristics);

		$this->out(__('* Successfully reset all characteristics', true));
		$this->_stop();
	}

/**
 * Checks and updates attributes on every package by
 * recursing through all letter folders and checking
 * each individual user
 *
 * Uses PCNTL extension in order to ensure that a given
 * characterization doesn't fail spectacularly. Sometimes a plugin
 * will require or include a file that doesn't exist, leading to a
 * fatal error
 * @return void
 * @author Jose Diaz-Gonzalez
 */
	function characterize() {
		if (!function_exists('pcntl_fork')) {
		    die("PCNTL functions not available on this PHP installation\n");
		}

		CakeLog::drop('database');

		$count = 0;

		$this->Package->Behaviors->detach('Searchable');
		$this->Package->enableSoftDeletable('find', false);
		$packages = $this->Package->find('list', array(
			// 'conditions' => array('id' => 8),
			'order' => 'id'
		));
		foreach ($packages as $id => $name) {
			$pid = pcntl_fork();
			if ($pid == -1) {
				die("PCNTL Unable to fork\n");
			} else if ( $pid == 0 ) {
				$status = SIG_ERR;

				// This is the child process.  Do something here.
				// Instead of calling exit(), we use posix_kill()
				$this->out(sprintf(__('* Processing %s', true), $name));
				$this->Package->getDatasource()->disconnect();
				$this->Package->getDatasource()->connect();

				if ($this->Package->characterize($id)) {
					$status = SIGKILL;
				}

				$this->Package->getDatasource()->disconnect();
				posix_kill(getmypid(), $status);
				return;
			} else {
				pcntl_wait($status);
				if ($status === SIGKILL) {
					$count++;
					continue;
				}
				$this->Package->getDatasource()->connect();
				$this->Package->broken($id);
				$this->Package->getDatasource()->disconnect();
			}
		}
		$this->out(sprintf(__('* Checked %s of %s repositories', true), $count, count($packages)));
	}

}
