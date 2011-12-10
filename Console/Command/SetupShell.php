<?php
/**
 * Setup Shell
 *
 * PHP version 5
 *
 * @category Shell
 * @package  Setup
 * @version  0.1
 * @author   Jose Diaz-Gonzalez <support@savant.be>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.josediazgonzalez.com
 */
App::uses('File', 'Utility');
App::uses('Folder', 'Utility');
App::uses('Security', 'Utility');

class SetupShell extends AppShell {

	public $folder = null;

/**
 * Main shell logic.
 *
 * @return void
 * @author John David Anderson
 */
	public function main() {
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
	protected function __run() {

		$validCommands = array('c', 'p', 'h', 'q');

		while (empty($this->command)) {
			$this->out("Setup Shell");
			$this->hr();
			$this->out("[C]reate Cache Directories");
			$this->out("[P]assword Generation");
			$this->out("[H]elp Menu");
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
				$this->create_cache_dirs();
				break;
			case 'p' :
				$this->password();
				break;
			case 'h' :
				$this->help();
				break;
			case 'q' :
				$this->out(__("Exit"));
				$this->_stop();
				break;
		}
	}

/**
 * Help
 *
 * @return void
 * @access public
 */
	public function help() {
		$this->out('Jose Diaz-Gonzalez. Setup Shell - http://josediazgonzalez.com');
		$this->hr();
		$this->out('This shell is a helper shell for many different kinds of tasks that might be performed by CakePackages');
		$this->out('');
		$this->hr();
		$this->out("Usage: cake setup");
		$this->out("       cake setup password YourCustomPassword");
		$this->out('');
	}

	public function create_cache_dirs() {
		if (!$this->folder) $this->folder = new Folder();
		$tmp_dir = trim(TMP);
		$this->folder->cd($tmp_dir);
		$tmp_folders = $this->folder->read();
		foreach (array('cache', 'logs', 'sessions', 'tests') as $tmp_folder) {
			if (in_array($tmp_folder, $tmp_folders[0])) {
				$this->folder->chmod($tmp_dir . DS . $tmp_folder, 0777, true);
				$this->out(sprintf(__('tmp/%s folder exists'), $tmp_folder));
				continue;
			}
			$this->folder->create($tmp_dir . DS . $tmp_folder);
			$this->folder->chmod($tmp_dir . DS . $tmp_folder, 0777, true);
			$this->out(sprintf(__('tmp/%s folder created'), $tmp_folder));
		}

		$cache_dir = trim(TMP . DS . 'cache');
		$this->folder->cd($cache_dir);
		$cache_folders = $this->folder->read();
		foreach (array('data', 'models', 'persistent', 'views') as $cache_folder) {
			if (in_array($cache_folder, $cache_folders[0])) {
				$this->folder->chmod($tmp_dir . DS . 'cache' . DS . $cache_folder, 0777, true);
				$this->out(sprintf(__('tmp/cache/%s folder exists'), $cache_folder));
				continue;
			}
			$this->folder->create($tmp_dir . DS . 'cache' . DS . $cache_folder);
			$this->folder->chmod($tmp_dir . DS . 'cache' . DS . $cache_folder, 0777, true);
			$this->out(sprintf(__('tmp/cache/%s folder created'), $cache_folder));
		}
	}

/**
 * Generates a password based upon input
 *
 * @return void
 * @author Jose Diaz-Gonzalez <support@savant.be>
 */
	public function password() {
		$password = (isset($this->args['0'])) ? $this->args['0'] : null;
		while (empty($password)) {
			$password = $this->in("What password would you like to hash?");
			if (!empty($password)) break;
			$this->out("Try again.");
		}
		$this->out(Security::hash($password, null, true));
	}
}
?>