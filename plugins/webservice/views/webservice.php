<?php
class WebserviceView extends Object {

/**
 * Determines whether native JSON extension is used for encoding.  Set by object constructor.
 *
 * @var boolean
 * @access public
 */
	var $json_useNative = false;

/**
 * XML document encoding
 *
 * @var string
 * @access private
 */
	var $xml_encoding = 'UTF-8';

/**
 * XML document version
 *
 * @var string
 * @access private
 */
	var $xml_version = '1.0';

/**
 * Array of parameter data
 *
 * @var array Parameter data
 */
	var $params = array();

/**
 * Variables for the view
 *
 * @var array
 * @access public
 */
	var $viewVars = array();

/**
 * List of variables to collect from the associated controller
 *
 * @var array
 * @access protected
 */
	var $__passedVars = array(
		'viewVars', 'params'
	);

/**
 * Constructor
 *
 * @param Controller $controller A controller object to pull View::__passedArgs from.
 * @param boolean $register Should the View instance be registered in the ClassRegistry
 * @return View
 */
	function __construct(&$controller, $register = true) {
		if (is_object($controller)) {
			$count = count($this->__passedVars);
			for ($j = 0; $j < $count; $j++) {
				$var = $this->__passedVars[$j];
				$this->{$var} = $controller->{$var};
			}
		}
		parent::__construct();

		if ($register) {
			ClassRegistry::addObject('view', $this);
		}
	}


	function render() {
		Configure::write('debug', 0);
		if (isset($this->viewVars['debugToolbarPanels'])) unset($this->viewVars['debugToolbarPanels']);
		if (isset($this->viewVars['debugToolbarJavascript'])) unset($this->viewVars['debugToolbarJavascript']);

		if ($this->params['url']['ext'] == 'json') {
			header("Pragma: no-cache"); 
			header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate"); 
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header("Last-Modified: " . gmdate('D, d M Y H:i:s') . ' GMT');
			header('Content-type: application/json');

			header("X-JSON: " . $this->object($this->viewVars));

			return $this->object($this->viewVars);
		}
		header('Content-type: application/xml');
		return $this->toXml($this->viewVars);
	}

/**
 * Dummy method
 * 
 * @deprecated deprecated in Webservice view
 */
	function renderLayout() {
	}

/**
 * Generates a JavaScript object in JavaScript Object Notation (JSON)
 * from an array
 *
 * ### Options
 *
 * - block - Wraps the return value in a script tag if true. Default is false
 * - prefix - Prepends the string to the returned data. Default is ''
 * - postfix - Appends the string to the returned data. Default is ''
 * - stringKeys - A list of array keys to be treated as a string.
 * - quoteKeys - If false treats $stringKeys as a list of keys **not** to be quoted. Default is true.
 * - q - The type of quote to use. Default is '"'.  This option only affects the keys, not the values.
 *
 * @param array $data Data to be converted
 * @param array $options Set of options: block, prefix, postfix, stringKeys, quoteKeys, q
 * @return string A JSON code block
 */
	function object($data = array(), $options = array()) {
		if (!empty($options) && !is_array($options)) {
			$options = array('block' => $options);
		} else if (empty($options)) {
			$options = array();
		}

		$defaultOptions = array(
			'block' => false, 'prefix' => '', 'postfix' => '',
			'stringKeys' => array(), 'quoteKeys' => true, 'q' => '"'
		);
		$options = array_merge($defaultOptions, $options, array_filter(compact(array_keys($defaultOptions))));

		if (is_object($data)) {
			$data = get_object_vars($data);
		}

		$out = $keys = array();
		$numeric = true;

		if ($this->json_useNative) {
			$rt = json_encode($data);
		} else {
			if (is_null($data)) {
				return 'null';
			}
			if (is_bool($data)) {
				return $data ? 'true' : 'false';
			}

			if (is_array($data)) {
				$keys = array_keys($data);
			}

			if (!empty($keys)) {
				$numeric = (array_values($keys) === array_keys(array_values($keys)));
			}

			foreach ($data as $key => $val) {
				if (is_array($val) || is_object($val)) {
					$val = $this->object(
						$val,
						array_merge($options, array('block' => false, 'prefix' => '', 'postfix' => ''))
					);
				} else {
					$quoteStrings = (
						!count($options['stringKeys']) ||
						($options['quoteKeys'] && in_array($key, $options['stringKeys'], true)) ||
						(!$options['quoteKeys'] && !in_array($key, $options['stringKeys'], true))
					);
					$val = $this->value($val, $quoteStrings);
				}
				if (!$numeric) {
					$val = $options['q'] . $this->value($key, false) . $options['q'] . ':' . $val;
				}
				$out[] = $val;
			}

			if (!$numeric) {
				$rt = '{' . implode(',', $out) . '}';
			} else {
				$rt = '[' . implode(',', $out) . ']';
			}
		}

		return $rt;
	}

/**
 * Converts a PHP-native variable of any type to a JSON-equivalent representation
 *
 * @param mixed $val A PHP variable to be converted to JSON
 * @param boolean $quoteStrings If false, leaves string values unquoted
 * @return string a JavaScript-safe/JSON representation of $val
 */
	function value($val, $quoteStrings = true) {
		switch (true) {
			case (is_array($val) || is_object($val)):
				$val = $this->object($val);
			break;
			case ($val === null):
				$val = 'null';
			break;
			case (is_bool($val)):
				$val = !empty($val) ? 'true' : 'false';
			break;
			case (is_int($val)):
				$val = $val;
			break;
			case (is_float($val)):
				$val = sprintf("%.11f", $val);
			break;
			default:
				$val = $this->escapeString($val);
				if ($quoteStrings) {
					$val = '"' . $val . '"';
				}
			break;
		}
		return $val;
	}

/**
 * Escape a string to be JavaScript friendly.
 *
 * List of escaped ellements:
 *	+ "\r\n" => '\n'
 *	+ "\r" => '\n'
 *	+ "\n" => '\n'
 *	+ '"' => '\"'
 *	+ "'" => "\\'"
 *
 * @param  string $script String that needs to get escaped.
 * @return string Escaped string.
 */
	function escapeString($string) {
		App::import('Core', 'Multibyte');
		$escape = array("\r\n" => "\n", "\r" => "\n");
		$string = str_replace(array_keys($escape), array_values($escape), $string);
		return $this->_utf8ToHex($string);
	}

/**
 * Encode a string into JSON.  Converts and escapes necessary characters.
 *
 * @return void
 */
	function _utf8ToHex($string) {
		$length = strlen($string);
		$return = '';
		for ($i = 0; $i < $length; ++$i) {
			$ord = ord($string{$i});
			switch (true) {
				case $ord == 0x08:
					$return .= '\b';
					break;
				case $ord == 0x09:
					$return .= '\t';
					break;
				case $ord == 0x0A:
					$return .= '\n';
					break;
				case $ord == 0x0C:
					$return .= '\f';
					break;
				case $ord == 0x0D:
					$return .= '\r';
					break;
				case $ord == 0x22:
				case $ord == 0x2F:
				case $ord == 0x5C:
				case $ord == 0x27:
					$return .= '\\' . $string{$i};
					break;
				case (($ord >= 0x20) && ($ord <= 0x7F)):
					$return .= $string{$i};
					break;
				case (($ord & 0xE0) == 0xC0):
					if ($i + 1 >= $length) {
						$i += 1;
						$return .= '?';
						break;
					}
					$charbits = $string{$i} . $string{$i + 1};
					$char = Multibyte::utf8($charbits);
					$return .= sprintf('\u%04s', dechex($char[0]));
					$i += 1;
					break;
				case (($ord & 0xF0) == 0xE0):
					if ($i + 2 >= $length) {
						$i += 2;
						$return .= '?';
						break;
					}
					$charbits = $string{$i} . $string{$i + 1} . $string{$i + 2};
					$char = Multibyte::utf8($charbits);
					$return .= sprintf('\u%04s', dechex($char[0]));
					$i += 2;
					break;
				case (($ord & 0xF8) == 0xF0):
					if ($i + 3 >= $length) {
					   $i += 3;
					   $return .= '?';
					   break;
					}
					$charbits = $string{$i} . $string{$i + 1} . $string{$i + 2} . $string{$i + 3};
					$char = Multibyte::utf8($charbits);
					$return .= sprintf('\u%04s', dechex($char[0]));
					$i += 3;
					break;
				case (($ord & 0xFC) == 0xF8):
					if ($i + 4 >= $length) {
					   $i += 4;
					   $return .= '?';
					   break;
					}
					$charbits = $string{$i} . $string{$i + 1} . $string{$i + 2} . $string{$i + 3} . $string{$i + 4};
					$char = Multibyte::utf8($charbits);
					$return .= sprintf('\u%04s', dechex($char[0]));
					$i += 4;
					break;
				case (($ord & 0xFE) == 0xFC):
					if ($i + 5 >= $length) {
					   $i += 5;
					   $return .= '?';
					   break;
					}
					$charbits = $string{$i} . $string{$i + 1} . $string{$i + 2} . $string{$i + 3} . $string{$i + 4} . $string{$i + 5};
					$char = Multibyte::utf8($charbits);
					$return .= sprintf('\u%04s', dechex($char[0]));
					$i += 5;
					break;
			}
		}
		return $return;
	}

/**
 * The main function for converting to an XML document.
 * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
 *
 * @param array $data
 * @param string $rootNodeName - what you want the root node to be - defaultsto data.
 * @param SimpleXMLElement $xml - should only be used recursively
 * @return string XML
 */
	public function toXML($data, $rootNodeName = 'ResultSet', &$xml = null) {
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1) ini_set('zend.ze1_compatibility_mode', 0);
		if (is_null($xml)) $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><{$rootNodeName} />");

		// loop through the data passed in.
		foreach ($data as $key => $value) {
			// no numeric keys in our xml please!
			$numeric = false;
			if (is_numeric($key)) {
				$numeric = 1;
				$key = $rootNodeName;
			}

			// delete any char not allowed in XML element names
			$key = preg_replace('/[^a-z0-9\-\_\.\:]/i', '', $key);

			// if there is another array found recrusively call this function
			if (is_array($value)) {
				$node = $this->isAssoc($value) || $numeric ? $xml->addChild($key) : $xml;

				// recrusive call.
				if ($numeric) $key = 'anon';
				$this->toXml($value, $key, $node);
			} else {
				// add single node.
				$value = htmlentities($value);
    			$xml->addChild($key, $value);
			}
		}

		//return $xml->asXML();
		// if you want the XML to be formatted, use the below instead to return the XML

		$doc = new DOMDocument('1.0');
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($xml->asXML());
		$doc->formatOutput = true;
		return $doc->saveXML();
	}


/**
 * Convert an XML document to a multi dimensional array
 * Pass in an XML document (or SimpleXMLElement object) and this recursively loops through and builds a representative array
 *
 * @param string $xml - XML document - can optionally be a SimpleXMLElement object
 * @return array ARRAY
 */
	public function toArray($xml) {
		if (is_string($xml)) $xml = new SimpleXMLElement($xml);

		$children = $xml->children();
		if (!$children) return (string) $xml;

		$arr = array();
		foreach ($children as $key => $node) {
			$node = $this->toArray($node);

			// support for 'anon' non-associative arrays
			if ($key == 'anon') $key = count($arr);

			// if the node is already set, put it into an array
			if (isset($arr[$key])) {
				if (!is_array($arr[$key]) || $arr[$key][0] == null) $arr[$key] = array($arr[$key]);
				$arr[$key][] = $node;
			} else {
				$arr[$key] = $node;
			}
		}
		return $arr;
	}

/**
 * Determine if a variable is an associative array
 *
 * @param mixed $variable variable to checked for associativity
 * @return boolean try if variable is an associative array, false if otherwise
 */
	public function isAssoc($variable) {
		return (is_array($variable) && 0 !== count(array_diff_key($variable, array_keys(array_keys($variable)))));
	}
}
?>