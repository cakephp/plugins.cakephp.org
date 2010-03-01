<?php
class LostController extends AppController {
	var $name = 'Lost';
	var $uses = array();

	function index() {
		$arguments = '';
		foreach ($this->params['pass'] as $argument) {
			$arguments .= $argument;
		}
		CakeLog::write('info', serialize($arguments));
	}
}
?>