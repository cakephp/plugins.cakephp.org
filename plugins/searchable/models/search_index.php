<?php
class SearchIndex extends SearchableAppModel {
	var $name = 'SearchIndex';
	var $useTable = 'search_index';

/**
 * Returns array of types (models) used in the Search Index with model name as
 * the key and the humanised form as the value.
 *
 * @return unknown
 */
  function getTypes() {
		// Read from cache
		$types = Cache::read('search_index_types');
		if ($types !== false) {
			return $types;
		}
		// If cache not valid generate types data
		$data = $this->find('all', array('fields' => array('DISTINCT(SearchIndex.model)', 'DISTINCT(SearchIndex.model)')));
		$data = Set::extract('/SearchIndex/model', $data);
		$types = array();
		foreach ($data as $type) {
			$types[$type] = Inflector::humanize(Inflector::tableize($type));
		}
		// Store types in cache
		Cache::write('search_index_types', $types);
		return $types;
	}
}
?>