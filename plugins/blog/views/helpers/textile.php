<?php
class TextileHelper extends AppHelper {
    function output($content, $lite = '', $encode = '', $noimage = '', $strict = '', $rel = '') {
		App::import('Vendor', 'Blog.Textile', array('file' => 'textile'.DS.'textile.php'));
		$textiler = &new Textile();
		return $textiler->TextileThis($content, $lite, $encode, $noimage, $strict, $rel);
	}
}
?>