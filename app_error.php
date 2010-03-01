<?php
class AppError extends ErrorHandler {

	public function __construct($method, $messages) {
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

	function lost() {
		$this->controller->redirect(array('controller' => 'lost', 'action' => 'index', $this->controller->params['url']['url']));
	}
}
?>