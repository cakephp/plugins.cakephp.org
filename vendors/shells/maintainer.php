<?php
/**
 * Maintainer Shell
 *
 * @package default
 * @author Jose Diaz-Gonzalez
 * @version $Id$
 **/
class MaintainerShell extends Shell {

/**
 * Contains tasks to load and instantiate
 *
 * @var array
 * @access public
 */
	var $tasks = array();

/**
 * Contains models to load and instantiate
 *
 * @var array
 * @access public
 */
	var $uses = array('Maintainer');

/**
 * Override main
 *
 * @access public
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

		$validCommands = array('r', 'q');

		while (empty($this->command)) {
			$this->out("Package Shell");
			$this->hr();
			$this->out("[R]esave");
			$this->out("[Q]uit");
			$temp = $this->in("What command would you like to perform?", $validCommands, 'i');
			if (in_array(strtolower($temp), $validCommands)) {
				$this->command = $temp;
			} else {
				$this->out("Try again.");
			}
		}

		switch ($this->command) {
			case 'r' :
				$this->maintainer_resave();
				break;
			case 'q' :
				$this->out(__("Exit", true));
				$this->_stop();
				break;
		}
	}


/**
 * Resave's each and every maintainer. Useful for
 * resetting their url info to a valid url
 *
 * @return void
 * @author Jose Diaz-Gonzalez
 */
	function resave() {
		$p_count = 0;
		$maintainers = $this->Maintainer->find('all', array(
			'contain' => false,
			'order' => array('Maintainer.username ASC')));
		foreach ($maintainers as $maintainer) {
			$p_count++;
			$this->out(sprintf(__('[Maintainer] %s', true), $maintainer['Maintainer']['username']));
			$this->Maintainer->save($maintainer);
		}
		$this->out(sprintf(__('* Resaved %s maintainers', true), $p_count));
	}

/**
 * Displays help contents
 *
 * @access public
 */
    function help() {
        $help = <<<TEXT
The Maintainer Shell 
---------------------------------------------------------------
Usage: cake maintainer <command> <arg1> <arg2>...
---------------------------------------------------------------
Params:


Commands:

    maintainer help
        shows this help message.

TEXT;
        $this->out($help);
        $this->_stop();
    }

}