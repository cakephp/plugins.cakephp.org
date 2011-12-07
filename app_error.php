<?php
class AppError extends ErrorHandler {

	public $_debug = 0;

	public function __construct($method, $messages) {
		$this->_debug = Configure::read('debug');
		Configure::write('debug', 1);
		parent::__construct($method, $messages);
	}
 
	public function _outputMessage($template) {
		if ($this->_debug == 0) {
			$template = 'error404';
		}
		$this->controller->render($template);
		$this->controller->afterFilter();
		echo $this->controller->output;
	}

}