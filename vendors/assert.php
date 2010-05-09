<?php
/**
 * undocumented class
 *
 * @package default
 * @access public
 */
class Assert{
/**
 * undocumented function
 *
 * @param unknown $a 
 * @param unknown $b 
 * @param unknown $info 
 * @param unknown $strict 
 * @return void
 * @access public
 */
	static function test($val, $expected, $info = array(), $strict = true) {
		$success = ($strict)
			? $val === $expected
			: $val == $expected;

		if ($success) {
			return true;
		}

		$calls = debug_backtrace();
		foreach ($calls as $call) {
			if ($call['file'] !== __FILE__) {
				$assertCall = $call;
				break;
			}
		}
		$triggerCall = current($calls);
		$type = Inflector::underscore($assertCall['function']);

		if (is_string($info)) {
			$info = array('type' => $info);
		}

		$info = am(array(
			'file' => $assertCall['file']
			, 'line' => $assertCall['line']
			, 'function' => $triggerCall['class'].'::'.$triggerCall['function']
			, 'assertType' => $type
			, 'val' => $val
			, 'expected' => $expected
		), $info);
		throw new AppException($info);
	}

/**
 * undocumented function
 *
 * @param unknown $val 
 * @return void
 * @access public
 */
	static function true($val, $info = array()) {
		return Assert::test($val, true, $info);
	}

/**
 * undocumented function
 *
 * @param unknown $val 
 * @param unknown $info 
 * @return void
 * @access public
 */
	static function false($val, $info = array()) {
		return Assert::test($val, false, $info);
	}

/**
 * undocumented function
 *
 * @param unknown $a 
 * @param unknown $b 
 * @param unknown $info 
 * @return void
 * @access public
 */
	static function equal($a, $b, $info = array()) {
		return Assert::test($a, $b, $info, false);
	}

/**
 * undocumented function
 *
 * @param unknown $a 
 * @param unknown $b 
 * @param unknown $info 
 * @return void
 * @access public
 */
	static function identical($a, $b, $info = array()) {
		return Assert::test($a, $b, $info, true);
	}

/**
 * undocumented function
 *
 * @return void
 * @access public
 */
	static function pattern($pattern, $val, $info = array()) {
		return Assert::test(preg_match($pattern, $val), true, am(array('pattern' => $pattern), $info));
	}

/**
 * undocumented function
 *
 * @param unknown $val 
 * @param unknown $info 
 * @return void
 * @access public
 */
	static function isEmpty($val, $info = array()) {
		return Assert::test(empty($val), true, $info);
	}

/**
 * undocumented function
 *
 * @param unknown $val 
 * @param unknown $info 
 * @return void
 * @access public
 */	
	static function notEmpty($val, $info = array()) {
		return Assert::test(empty($val), false, $info);
	}

/**
 * undocumented function
 *
 * @param unknown $val 
 * @param unknown $info 
 * @return void
 * @access public
 */
	static function isNumeric($val, $info = array()) {
		return Assert::test(is_numeric($val), true, $info);
	}

/**
 * undocumented function
 *
 * @param unknown $val 
 * @param unknown $info 
 * @return void
 * @access public
 */
	static function notNumeric($val, $info = array()) {
		return Assert::test(is_numeric($val), false, $info);
	}

/**
 * undocumented function
 *
 * @param unknown $val 
 * @param unknown $info 
 * @return void
 * @access public
 */
	static function isInteger($val, $info = array()) {
		return Assert::test(is_int($val), true, $info);
	}

/**
 * undocumented function
 *
 * @param unknown $val 
 * @param unknown $info 
 * @return void
 * @access public
 */
	static function notInteger($val, $info = array()) {
		return Assert::test(is_int($val), false, $info);
	}

/**
 * undocumented function
 *
 * @return void
 * @access public
 */
	static function isIntegerish($val, $info = array()) {
		return Assert::test(is_int($val) || ctype_digit($val), true, $info);
	}

/**
 * undocumented function
 *
 * @return void
 * @access public
 */
	static function notIntegerish($val, $info = array()) {
		return Assert::test(is_int($val) || ctype_digit($val), false, $info);
	}

/**
 * undocumented function
 *
 * @param unknown $val 
 * @param unknown $info 
 * @return void
 * @access public
 */
	static function isObject($val, $info = array()) {
		return Assert::test(is_object($val), true, $info);
	}

/**
 * undocumented function
 *
 * @param unknown $val 
 * @param unknown $info 
 * @return void
 * @access public
 */
	static function notObject($val, $info = array()) {
		return Assert::test(is_object($val), false, $info);
	}

/**
 * undocumented function
 *
 * @param unknown $val 
 * @param unknown $info 
 * @return void
 * @access public
 */
	static function isBoolean($val, $info = array()) {
		return Assert::test(is_bool($val), true, $info);
	}

/**
 * undocumented function
 *
 * @param unknown $val 
 * @param unknown $info 
 * @return void
 * @access public
 */
	static function notBoolean($val, $info = array()) {
		return Assert::test(is_bool($val), false, $info);
	}

/**
 * undocumented function
 *
 * @param unknown $val 
 * @param unknown $info 
 * @return void
 * @access public
 */
	static function isString($val, $info = array()) {
		return Assert::test(is_string($val), true, $info);
	}

/**
 * undocumented function
 *
 * @param unknown $val 
 * @param unknown $info 
 * @return void
 * @access public
 */
	static function notString($val, $info = array()) {
		return Assert::test(is_string($val), false, $info);
	}

/**
 * undocumented function
 *
 * @param unknown $val 
 * @param unknown $info 
 * @return void
 * @access public
 */
	static function isArray($val, $info = array()) {
		return Assert::test(is_array($val), true, $info);
	}

/**
 * undocumented function
 *
 * @param unknown $val 
 * @param unknown $info 
 * @return void
 * @access public
 */
	static function notArray($val, $info = array()) {
		return Assert::test(is_array($val), false, $info);
	}
}

App::import('Core', 'Error');
class AppExceptionHandler extends ErrorHandler {
/**
 * New Exception handler, renders an error view, then quits the application.
 *
 * @param object $Exception AppException object to handle
 * @return void
 * @access public
 */
	static function handleException($Exception) {
		$Exception->render();
		exit;
	}
/**
 * Throws an AppExcpetion if there is no db connection present
 *
 * @return void
 * @access public
 */
	function missingConnection() {
		throw new AppException('db_connect');
	}
}
set_exception_handler(array('AppExceptionHandler', 'handleException'));

/**
 * undocumented class
 *
 * @package default
 * @access public
 */
class AppException extends Exception {
/**
 * Details about what caused this Exception
 *
 * @var array
 * @access public
 */
	var $info = null;
/**
 * undocumented function
 *
 * @param mixed $info A string desribing the type of this exception, or an array with information
 * @return void
 * @access public
 */
	function __construct($info = 'unknown') {
		if (!is_array($info)) {
			$info = array('type' => $info);
		}
		$this->info = $info;
	}
/**
 * Renders a view with information about what caused this Exception. $info['type'] is used to determine what
 * view inside of views/exceptions/ is used. The default is 'unknown.ctp'.
 *
 * @return void
 * @access public
 */
	function render() {
		$info = am($this->where(), $this->info);

		$Controller = new Controller();
		$Controller->viewPath = 'exceptions';
		$Controller->layout = (file_exists(VIEWS . 'layouts' . DS . 'exception.ctp')) ? 'exception' : 'default';

		$Dispatcher = new Dispatcher();
		$Controller->base = $Dispatcher->baseUrl();
		$Controller->webroot = $Dispatcher->webroot;
		
		$Controller->set(compact('info'));
		$View = new View($Controller);

		$view = @$info['type'];
		if (!file_exists(VIEWS . 'errors' . DS . $view . '.ctp')) {
			$view = 'unknown';
		}
		Configure::write('debug', 0);
		header($this->statusCode($info['type']));
		
		echo $View->render($view);
	}

	function statusCode($type = null) {
		switch($type) {
			case '400' : return "HTTP/1.0 400 Bad Request";break;
			case '401' : return "HTTP/1.0 401 Unauthorized";break;
			case '402' : return "HTTP/1.0 402 Payment Required";break;
			case '403' : return "HTTP/1.0 403 Forbidden";break;
			case '404' : return "HTTP/1.0 404 Not Found";break;
			case '405' : return "HTTP/1.0 405 Method Not Allowed";break;
			case '406' : return "HTTP/1.0 406 Not Acceptable";break;
			case '407' : return "HTTP/1.0 407 Proxy Authentication Required";break;
			case '408' : return "HTTP/1.0 408 Request Timeout";break;
			case '409' : return "HTTP/1.0 409 Conflict";break;
			case '410' : return "HTTP/1.0 410 Gone";break;
			case '411' : return "HTTP/1.0 411 Length Required";break;
			case '412' : return "HTTP/1.0 412 Precondition Failed";break;
			case '413' : return "HTTP/1.0 413 Request Entity Too Large";break;
			case '414' : return "HTTP/1.0 414 Request-URI Too Long";break;
			case '415' : return "HTTP/1.0 415 Unsupported Media Type";break;
			case '416' : return "HTTP/1.0 416 Requested Range Not Satisfiable";break;
			case '417' : return "HTTP/1.0 417 Expectation Failed";break;
			case '418' : return "HTTP/1.0 418 I'm a teapot";break;
			case '421' : return "HTTP/1.0 421 There are too many connections from your internet address";break;
			case '422' : return "HTTP/1.0 422 Unprocessable Entity";break;
			case '423' : return "HTTP/1.0 423 Locked";break;
			case '424' : return "HTTP/1.0 424 Failed Dependency";break;
			case '425' : return "HTTP/1.0 425 Unordered Collection";break;
			case '426' : return "HTTP/1.0 426 Upgrade Required";break;
			case '449' : return "HTTP/1.0 449 Retry With";break;
			case '450' : return "HTTP/1.0 450 Blocked by Windows Parental Controls";break;
			case '500' : return "HTTP/1.0 500 Internal Server Error";break;
			case '501' : return "HTTP/1.0 501 Not Implemented";break;
			case '502' : return "HTTP/1.0 502 Bad Gateway";break;
			case '503' : return "HTTP/1.0 503 Service Unavailable";break;
			case '504' : return "HTTP/1.0 504 Gateway Timeout";break;
			case '505' : return "HTTP/1.0 505 HTTP Version Not Supported";break;
			case '506' : return "HTTP/1.0 506 Variant Also Negotiates";break;
			case '507' : return "HTTP/1.0 507 Insufficient Storage";break;
			case '509' : return "HTTP/1.0 509 Bandwidth Limit Exceeded";break;
			case '510' : return "HTTP/1.0 510 Not Extended";break;
 			default : return "HTTP/1.0 530 User access denied";
		}
	}
/**
 * Returns an array describing where this Exception occured
 *
 * @return array
 * @access public
 */
	function where() {
		return array(
			'function' => $this->getClass().'::'.$this->getFunction()
			, 'file' => $this->getFile()
			, 'line' => $this->getLine()
			, 'url' => $this->getUrl()
		);
	}
/**
 * Returns the url where this Exception occured
 *
 * @return string
 * @access public
 */
	function getUrl($full = true) {
		return Router::url(array('full_base' => $full));
	}
/**
 * Returns the class where this Exception occured
 *
 * @return void
 * @access public
 */
	function getClass() {
		$trace = $this->getTrace();
		return $trace[0]['class'];
	}
/**
 * Returns the function where this Exception occured
 *
 * @return void
 * @access public
 */
	function getFunction() {
		$trace = $this->getTrace();
		return $trace[0]['function'];
	}
}
?>