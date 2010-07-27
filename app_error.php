<?php
/**
 * Application Error class
 *
 * Contains Application Specific cakeErrors
 *
 * @package       app
 */
class AppError extends ErrorHandler {

/**
 * Output message
 *
 * Does not output debug info on errors
 *
 * @param string $action Action name to render
 * @access protected
 * @author Jose Diaz-Gonzalez
 */
	function _outputMessage($template) {
		Configure::write('debug', 0);
		$this->controller->render($template);
		$this->controller->afterFilter();
		echo $this->controller->output;
	}

/**
 * Renders the Failed Assertion web page.
 *
 * @param array $params Parameters for controller
 * @access public
 * @author Jose Diaz-Gonzalez
 */
	function assertion($params) {
		extract($params, EXTR_OVERWRITE);
		$this->controller->set(array(
			'code' => '412',
			'name' => 'Precondition Failed',
			'message' => 'An assertion was made and the condition failed',
			'file' => $file,
			'line' => $line,
			'function' => $function,
			'assertType' => $assertType,
			'val' => $val,
			'expected' => $expected
		));
		$this->_outputMessage('assertion');
	}

/**
 * Renders the Missing Action web page.
 *
 * @param array $params Parameters for controller
 * @access public
 * @author Jose Diaz-Gonzalez
 */
	function missingMethod($params) {
		extract($params, EXTR_OVERWRITE);

		$this->controller->set(array(
			'className' => $className,
			'methodName' => $methodName,
			'parameters' => $parameters,
			'parentClass' => $parentClass,
			'path' => $path,
			'title' => __('Missing Method in Class', true)
		));
		$this->_outputMessage('missingMethod');
	}

/**
 * Renders the Missing Model Method web page
 *
 * @param array $params Parameters for controller
 * @access public
 * @author Jose Diaz-Gonzalez
 */
	function missingModelMethod($params) {
		extract($params, EXTR_OVERWRITE);

		$this->missingMethod(array(
			'className' => $className,
			'methodName' => $methodName,
			'parameters' => $parameters,
			'parentClass' => 'AppModel',
			'path' => 'models'
		));
	}

/**
 * Renders the Uninitialized Class web page.
 *
 * @param array $params Parameters for controller
 * @access public
 * @author Jose Diaz-Gonzalez
 */
	function uninitializedClass($params) {
		extract($params, EXTR_OVERWRITE);

		$this->controller->set(array(
			'className' => $className,
			'title' => __('Failed to initialize Class', true)
		));
		$this->_outputMessage('uninitializedClass');
	}
}
?>