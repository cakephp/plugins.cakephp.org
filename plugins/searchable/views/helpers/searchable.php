<?php
class SearchableHelper extends AppHelper {
	var $helpers = array('Html', 'Text');

	function snippets($data) {
		$data = json_decode($data, true);
		$term = (isset($this->data['SearchIndex']['term'])) ? trim($this->data['SearchIndex']['term']) : '';
		$snippets = '';
		while (strlen($snippets) < 255 && $value = next($data)) {
			$snippets .= ' ' . $this->Text->highlight($this->Text->excerpt($value, $term, 20), $term);
		}
		return $snippets;
	}
}
?>
