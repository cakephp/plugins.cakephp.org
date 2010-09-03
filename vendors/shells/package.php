<?php
class PackageShell extends Shell {

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

		$validCommands = array('a', 'c', 'e', 'f', 'g', 'r', 'm', 's', 'u', 'q');

		while (empty($this->command)) {
			$this->out("Package Shell");
			$this->hr();
			$this->out("[A]dd missing Attributes");
			$this->out("[C]heck Characteristics");
			$this->out("[E]xistence Check");
			$this->out("[F]ix Repository Urls");
			$this->out("[G]it Clone Repositories");
			$this->out("[M]aintainer Resave");
			$this->out("[R]eset Characteristics");
			$this->out("[S]specific User/Repo");
			$this->out("[U]pdate Repositories");
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
				$this->add_missing_attributes();
				break;
			case 'c' :
				$this->check_characteristics();
				break;
			case 'e' :
				$this->existence_check();
				break;
			case 'f' :
				$this->fix_repository_urls();
				break;
			case 'g' :
				$this->git_clone_repositories();
				break;
			case 'r' :
				$this->reset_characteristics();
				break;
			case 's' :
				$this->check_characteristics_for_repository();
				break;
			case 'u' :
				$this->update_repositories();
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
	function add_missing_attributes() {
		$packages = $this->Package->find('all', array(
			'contain' => array('Maintainer' => array('id', 'username')),
			'fields' => array('id', 'name'),
			'order' => array('Package.name ASC')));

		$this->Package->Behaviors->detach('Searchable');
		$Github = ClassRegistry::init('Github');
		$count = 0;
		foreach ($packages as $package) {
			sleep(1);
			$repo = $Github->find('repos_show_single', array(
				'username' => $package['Maintainer']['username'],
				'repo' => $package['Package']['name']
			));
			if (!$repo || !isset($repo['Repository'])) {
				$this->out(sprintf(__('* Failed to update %s', true), $package['Package']['name']));
				continue;
			}
			$tmp = $repo['Repository']['homepage'];
			if (isset($repo['Repository']['homepage']['value'])) {
				$package['Package']['homepage'] = (is_array($repo['Repository']['homepage'])) ?
					$repo['Repository']['homepage']['value'] : $repo['Repository']['homepage'];
			} else {
				$package['Package']['homepage'] = $repo['Repository']['url'];
			}
			if (empty($repo['Repository']['homepage'])) $repo['Repository']['homepage'] = $tmp;
			if (isset($repo['Repository']['description'])) {
				$package['Package']['description'] = $repo['Repository']['description'];
			}
			if ($this->Package->save($package)) {
				$this->out(sprintf(__('* Updated %s', true), $package['Package']['name']));
				$count++;
				continue;
			}
			$this->out(sprintf(__('* Failed to update %s', true), $package['Package']['name']));
		}
		$p_count = count($packages);
		$this->out(sprintf(__('* Updated %s of %s packages', true), $count, $p_count));
	}

	function existence_check() {
		$this->Package->Behaviors->detach('Searchable');
		$packages = $this->Package->find('all', array(
			'contain' => array('Maintainer' => array('id', 'username')),
			'fields' => array('id', 'name'),
			'order' => array('Package.name ASC')));
		$SearchIndex = ClassRegistry::init('Searchable.SearchIndex');
		foreach ($packages as $package) {
			sleep(1);
			$exists = $this->Package->checkExistenceOf($package);
			if (!$exists) {
				$this->out(sprintf(__('* Deleting record %s', true), $package['Package']['id']));
				$result = $this->Package->delete($package['Package']['id']);
				if ($result) continue;

				$search_index = $SearchIndex->find('first', array(
					'conditions' => array(
						'SearchIndex.model' => 'Package',
						'SearchIndex.foreign_key' => $package['Package']['id']
				)));
				$search_index['SearchIndex']['active'] = 0;
				$SearchIndex->save($search_index);
				$this->out(sprintf(__('* Record %s deleted', true), $package['Package']['id']));
				continue;
			}
			$this->out(sprintf(__('* Record %s exists', true), $package['Package']['id']));
		}
	}

/**
 * Git clones all the repositories from their respective remote
 * locations
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 */
	function git_clone_repositories() {
		$p_count = 0;
		$tmp_dir = trim(TMP);
		$repo_dir = trim(TMP . 'repos');

		if (!$this->folder) $this->folder = new Folder();
		$this->folder->cd($tmp_dir);
		$existing_files_and_folders = $this->folder->read();
		if (!in_array('repos', $existing_files_and_folders['0'])) {
			$this->folder->create($repo_dir);
		}

		$packages = $this->Package->find('all', array(
			'contain' => array('Maintainer' => array('id', 'username')),
			'fields' => array('id', 'name', 'repository_url'),
			'order' => array('Package.id ASC')
		));

		foreach ($packages as $package) {
			$p_count++;
			$repo_url = $package['Package']['repository_url'];
			$clone_path = strtolower($package['Maintainer']['username'][0]) . DS;
			$clone_path .= $package['Maintainer']['username'] . DS . $package['Package']['name'];
			$this->out(sprintf(__("* Downloading package to %s/%s...", true), $repo_dir, $clone_path));
			$this->out(shell_exec("cd {$repo_dir} ; git clone {$repo_url} {$clone_path}"));
		}
		$this->out(sprintf(__('* Downloaded %s repositories', true), $p_count));
	}

/**
 * Recurses through all repositories and updates them
 * where possible
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 */
	function update_repositories() {
		$p_count = 0;
		$repo_dir = trim(TMP . 'repos');
		if (!$this->folder) $this->folder = new Folder();

		foreach (range('a', 'z') as $letter) {
			$this->folder->cd($repo_dir . DS . $letter);
			$user_folders = $this->folder->read();
			foreach ($user_folders['0'] as $user_folder) {
				$this->folder->cd($repo_dir . DS . $letter . DS . $user_folder);
				$repositories = $this->folder->read();
				foreach ($repositories['0'] as $repository) {
					$p_count++;
					$repository_path = $repo_dir . DS . $letter . DS . $user_folder . DS . $repository;
					$this->out(sprintf(__("Updating repository %s...", true), $repository));
					$this->out(shell_exec("cd {$repository_path} ; git pull"));
				}
			}
		}
		$this->out(sprintf(__('* Updated %s repositories', true), $p_count));
	}

/**
 * Fixes clone urls (according to github) for all
 * repositories, regardless of their working status
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 */
	function fix_repository_urls() {
		$p_count = 0;
		$update_count = 0;

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

		$this->out(sprintf(__('* Successfully updated %s out of %s package urls', true), $update_count, $p_count));
		$this->_stop();
	}

/**
 * Resets all 'contains' attributes for all packages
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 */
	function reset_characteristics() {
		$p_count = 0;
		$update_count = 0;
		$characteristics = array(
			'contains_model', 'contains_datasource', 'contains_behavior', 'contains_controller',
			'contains_component', 'contains_view', 'contains_helper', 'contains_theme', 'contains_vendor',
			'contains_shell', 'contains_test', 'contains_lib', 'contains_resource', 'contains_config'
		);

		foreach ($characteristics as $characteristic) {
			$this->out(sprintf(__('Resetting %s', true), $characteristic));
			$this->Package->updateAll(array("Package.{$characteristic}" => 0));
		}

		$this->out(__('* Successfully reset all characteristics', true));
		$this->_stop();
	}

/**
 * Checks and updates attributes on every package by
 * recursing through all letter folders and checking
 * each individual user
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 */
	function check_characteristics() {
		$packages = $this->Package->find('list', array(
			'order' => array('Package.id ASC')
		));

		$count = 0;
		foreach ($packages as $i => $package) {
			$this->out(sprintf(__('* Setting up repository for %s', true), $package));
			$this->Package->setupRepoDirectory($i);
			$this->out(__('* Classifying repository', true));
			$characteristics = $this->Package->classifyRepository($i);
			if (!$characteristics) {
				$this->out(__('* Classification failed!', true));
				continue;
			}
			foreach ($characteristics as $characteristic) {
				$this->out(sprintf(__('** %s', true), Inflector::humanize($characteristic)));
			}
			$count++;
		}
		$this->out(sprintf(__('* Checked %s repositories', true), $count));
	}

}
