<?php
App::import('Core', 'Object');

/**
 * undocumented class
 *
 * @package default
 * @access public
 */
class Assert extends Object {
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

		$info = array_merge(array(
			'file' => $assertCall['file']
			, 'line' => $assertCall['line']
			, 'function' => $triggerCall['class'].'::'.$triggerCall['function']
			, 'assertType' => $type
			, 'val' => $val
			, 'expected' => $expected
		), $info);
		parent::cakeError('assertion', $info);
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
		return Assert::test(preg_match($pattern, $val), true, array_merge(array('pattern' => $pattern), $info));
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
?>