<?php
class AppError extends ErrorHandler {

	function __construct($method, $messages) {
		App::import('Core', 'Sanitize');
		static $__previousError = null;

		if ($__previousError != array($method, $messages)) {
			$__previousError = array($method, $messages);
			$this->controller =& new CakeErrorController();
		} else {
			$this->controller =& new Controller();
			$this->controller->viewPath = 'errors';
		}

		$options = array('escape' => false);
		$messages = Sanitize::clean($messages, $options);

		if (!isset($messages[0])) {
			$messages = array($messages);
		}

		if (method_exists($this->controller, 'apperror')) {
			return $this->controller->appError($method, $messages);
		}

		if (!in_array(strtolower($method), array_map('strtolower', get_class_methods($this)))) {
			$method = 'error';
		}

		if ($method !== 'error') {
			if (Configure::read('debug') == 0) {
				$parentClass = get_parent_class($this);
				if (strtolower($parentClass) != 'errorhandler') {
					$method = 'error404';
				}
				$parentMethods = array_map('strtolower', get_class_methods($parentClass));
				if (in_array(strtolower($method), $parentMethods)) {
					$method = 'error404';
				}
				if (isset($code) && $code == 500) {
					$method = 'error500';
				}
			}
		}

		$this->dispatchMethod($method, $messages);
		$this->_stop();
	}

	function missingController($params) {
		$this->lost();
	}
	
	function missingAction($params) {
		$this->lost();
	}

	function missingView($params) {
		$this->lost();
	}

	function error404($params) {
		$this->lost();
	}

	function lost() {
		echo $this->controller->requestAction(
			array('plugin' => null, 'controller' => 'lost', 'action' => 'index'), 
			array('pass' => array($this->controller->params['url']['url']))
		);
	}
}
?>