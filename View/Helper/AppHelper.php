<?php
App::uses('Helper', 'View');

class AppHelper extends Helper {

/**
 * Helper method to set custom information into a $NAME_for_layout variable
 *
 * @param string $name name of $for_layout variable
 * @param mixed $content Contents to set for_layout
 */
	public function for_layout($name, $content) {
		$this->_View->set("{$name}_for_layout", $content);
	}

/**
 * Convenience method to set a title_for_layout and h2_for_layout
 *
 * @param string $contents Contents
 * @param string $alternate Alternative if $contents are empy
 */
	public function h2($contents, $alternate = null) {
		$alt = trim($contents);
		if ((strlen($alt) === 0) && isset($alternate)) {
			$contents = $alternate;
		}

		$this->for_layout('title', $contents);
		$this->for_layout('h2', "<h2>{$contents}</h2>");
	}

}