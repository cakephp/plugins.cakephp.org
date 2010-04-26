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
		if(!empty($this->params[0])) {
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

		$validCommands = array('c', 'd', 'f', 'r', 's', 'u', 'q');

		while (empty($this->command)) {
			$this->out("Package Shell");
			$this->hr();
			$this->out("[C]heck Characteristics");
			$this->out("[D]ownload Repositories");
			$this->out("[F]ix Repository Urls");
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
			case 'c' :
				$this->check_characteristics();
				break;
			case 'd' :
				$this->download_repositories();
				break;
			case 'f' :
				$this->fix_repository_urls();
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

	function download_repositories() {
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
			'order' => array('Package.id ASC')));

		foreach ($packages as $package) {
			$p_count++;
			$repo_url = $package['Package']['repository_url'];
			$clone_path = strtolower($package['Maintainer']['username'][0]) . DS . 
			$clone_path = $package['Maintainer']['username'] . DS . $package['Package']['name'];
			$this->out(sprintf(__("Downloading package to %s/%s...", true), $repo_dir, $clone_path));
			$this->out(shell_exec("cd {$repo_dir} ; git clone {$repo_url} {$clone_path}"));
		}
		$this->out(sprintf(__('Downloaded %s repositories', true), $p_count));
	}

	function update_repositories() {
		$p_count = 0;
		$repo_dir = trim(TMP . 'repos');
		if (!$this->folder) $this->folder = new Folder();

		foreach(range('a', 'z') as $letter) {
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
		$this->out(sprintf(__('Updated %s repositories', true), $p_count));
	}

	function fix_repository_urls() {
		$p_count = 0;
		$update_count = 0;

		$packages = $this->Package->find('all', array(
			'contain' => array('Maintainer'),
			'order' => array('Package.id ASC')));

		foreach ($packages as $package) {
			$this->out(sprintf(
				__('Updating package id %s named %s', true),
				$package['Package']['id'],
				$package['Package']['name']));
			$p_count++;
			$package['Package']['repository_url'] = "git://github.com/{$package['Maintainer']['username']}/{$package['Package']['name']}";

			if ($this->Package->save($package)) $update_count++;
		}

		$this->out(sprintf(__('Successfully updated %s out of %s package urls', true), $update_count, $p_count));
		$this->_stop();
	}

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

		$this->out(__('Successfully reset all characteristics', true));
		$this->_stop();
	}

	function check_characteristics() {
		$p_count = 0;
		$repo_dir = trim(TMP . 'repos');
		if (!$this->folder) $this->folder = new Folder();

		$this->folder->cd($repo_dir);
		$folders = $this->folder->read();

		foreach($folders[0] as $letter) {
			$this->folder->cd($repo_dir . DS . $letter);
			$user_folders = $this->folder->read();
			foreach ($user_folders['0'] as $user_folder) {
				$p_count += $this->check_characteristics_for_user($user_folder);
			}
		}
		$this->out(sprintf(__('Checked %s repositories', true), $p_count));
	}

	function check_characteristics_for_user($user_folder) {
		$p_count = 0;
		$repo_dir = trim(TMP . 'repos');

		if (!$this->folder) $this->folder = new Folder();
		$this->folder->cd($repo_dir . DS . strtolower($user_folder[0]) . DS . $user_folder);
		$repositories = $this->folder->read();

		foreach ($repositories['0'] as $repository) {
			if ($this->check_characteristics_for_repository($user_folder, $repository)) $p_count++;
		}
		$this->out(sprintf(__('[User] %s for %s', true), $p_count, $user_folder));
		return $p_count;
	}

	function check_characteristics_for_repository($user_folder = null, $repository = null) {
		if (!$user_folder || !$repository) return false;

		$repo_dir = trim(TMP . 'repos');
		$characteristics = $this->_classify_repository(
			$repo_dir . DS . strtolower($user_folder[0]) . DS . $user_folder . DS . $repository);
		$package = $this->Package->find('first', array(
			'contain' => 'Maintainer',
			'conditions' => array(
				'Maintainer.username' => $user_folder,
				'Package.name' => $repository)));
		foreach ($characteristics as $characteristic) {
			$package['Package'][$characteristic] = 1;
		}
		unset($package['Package']['tags']);
		return $this->Package->save($package);
	}

	function _classify_repository($repository_path = null) {
		if (!$repository_path) return false;

		$characteristics = array();
		if (!$this->folder) $this->folder = new Folder();
		$this->folder->cd($repository_path);
		$contents = $this->folder->read();

		if (in_array('app', $contents[0])) {
			$characteristics[] = 'contains_app';
			$this->folder->cd($repository_path . DS . 'app');
			$contents = $this->folder->read();
		}
		$characteristics = array_merge($this->_classify_contents($repository_path, $contents), $characteristics);

		return $characteristics;
	}

	function _classify_contents($repository_path, $contents = array()) {
		$characteristics = array();
		$resources = null;
		if (in_array('models', $contents[0])) {
			// We might have some Models
			$this->folder->cd($repository_path . DS . 'models');
			$model_contents = $this->folder->read();
			if (!empty($model_contents[1])) {
				// Has Models. Probably
				$characteristics[] = 'contains_model';
			}
			if (in_array('datasources', $model_contents[0])) {
				$this->folder->cd($repository_path . DS . 'models' . DS . 'datasources');
				$datasource_contents = $this->folder->read();
				if (in_array('dbo', $datasource_contents[0])) {
					$this->folder->cd($repository_path . DS . 'models' . DS . 'datasources' . DS . 'dbo');
					$dbo_contents = $this->folder->read();
					if (!empty($dbo_contents[1])) {
						$characteristics[] = 'contains_datasource';
					}
				}
				if (!empty($datasource_contents[1]) && !in_array('contains_datasource', $characteristics)) {
					$characteristics[] = 'contains_datasource';
				}
			}
			if (in_array('behaviors', $model_contents[0])) {
				$this->folder->cd($repository_path . DS . 'models' . DS . 'behaviors');
				$behavior_contents = $this->folder->read();
				if (!empty($behavior_contents[1])) {
					$characteristics[] = 'contains_behavior';
				}
			}
		}
		if (in_array('controllers', $contents[0])) {
			$this->folder->cd($repository_path . DS . 'controllers');
			$controller_contents = $this->folder->read();
			if (!empty($controller_contents[1])) {
				$characteristics[] = 'contains_controller';
			}
			if (in_array('components', $controller_contents[0])) {
				$this->folder->cd($repository_path . DS . 'controllers' . DS . 'components');
				$component_contents = $this->folder->read();
				if (!empty($component_contents[1])) {
					$characteristics[] = 'contains_component';
				}
			}
		}
		if (in_array('views', $contents[0])) {
			$this->folder->cd($repository_path . DS . 'views');
			$view_contents = $this->folder->read();
			if (in_array('helpers', $view_contents[0])) {
				$this->folder->cd($repository_path . DS . 'views' . DS . 'helpers');
				$helper_contents = $this->folder->read();
				if (!empty($helper_contents[1])) {
					$characteristics[] = 'contains_helper';
				}
				unset($view_contents['helpers']);
			}
			if (in_array('themed', $view_contents[0])) {
				$this->folder->cd($repository_path . DS . 'views' . DS . 'themed');
				$theme_contents = $this->folder->read();
				if (!empty($theme_contents[0])) {
					$characteristics[] = 'contains_theme';
				}
				unset($view_contents['themed']);
			}
			if (in_array('elements', $view_contents[0])) {
				unset($view_contents['elements']);
			}

			if (!empty($view_contents[0])) {
				$characteristics[] = 'contains_view';
			}
		}
		if (in_array('vendors', $contents[0])) {
			$this->folder->cd($repository_path . DS . 'vendors');
			$vendor_contents = $this->folder->read();
			if (in_array('shells', $vendor_contents[0])) {
				$this->folder->cd($repository_path . DS . 'vendors' . DS . 'shells');
				$shell_contents = $this->folder->read();
				if (!empty($shell_contents[1])) {
					$characteristics[] = 'contains_shell';
				}
				unset($vendor_contents['shell']);
			}
			if (in_array('css', $vendor_contents[0])) {
				$resources = true;
				unset($vendor_contents['css']);
			}
			if (in_array('js', $vendor_contents[0])) {
				$resources = true;
				unset($vendor_contents['js']);
			}
			if (in_array('img', $vendor_contents[0])) {
				$resources = true;
				unset($vendor_contents['img']);
			}
			if (!empty($vendor_contents[0]) || !empty($vendor_contents[1])) {
				$characteristics[] = 'contains_vendor';
			}
		}
		if (in_array('tests', $contents[0])) {
			$characteristics[] = 'contains_test';
		}
		if (in_array('libs', $contents[0])) {
			$characteristics[] = 'contains_lib';
		}
		if (in_array('config', $contents[0])) {
			$characteristics[] = 'contains_config';
		}
		if (in_array('webroot', $contents[0]) || $resources) {
			$characteristics[] = 'contains_resources';
		}
		return $characteristics;
	}
}
?>