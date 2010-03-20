<?php
class SearchIndexesController extends SearchableAppController {
	var $name = 'SearchIndexes';
	var $paginate = array('SearchIndex' => array('limit' => 10));
	var $helpers = array('Searchable.Searchable', 'Text');

	function index($term = null) {
		// Redirect with search data in the URL in pretty format
		if (!empty($this->data)) {
			$redirect = array();
			$redirect['plugin'] = 'searchable';
			$redirect['controller'] = 'search_indexes';
			$redirect['action'] = 'index';
			$redirect['type'] = 'All';
			if (isset($this->data['SearchIndex']['type']) && !empty($this->data['SearchIndex']['type'])) {
				$redirect['type'] = $this->data['SearchIndex']['type'];
			} elseif (isset($this->params['type']) && $this->params['type'] != 'All') {
				$redirect['type'] = $this->params['type'];
			}
			if (isset($this->data['SearchIndex']['term'])) {
				$redirect['term'] = $this->data['SearchIndex']['term'];
			}
			$this->redirect($redirect);
		}

		$term = (!$term && isset($this->params['term'])) ? $this->params['term'] : $term;
		// Add default scope condition
		// Add published condition NULL or < NOW()
		$this->paginate = array(
			'SearchIndex' => array(
				'conditions' => array(
					'SearchIndex.active' => 1,
					'OR' => array(
						array('SearchIndex.published' => null),
						array('SearchIndex.published <= ' => date('Y-m-d H:i:s'))))));

		// Add type condition if not All
		if (isset($this->params['type']) && $this->params['type'] != 'All') {
			$this->data['SearchIndex']['type'] = $this->params['type'];
			$this->paginate['SearchIndex']['conditions']['model'] = $this->params['type'];
		}

    	// Add term condition, and sorting
		if (isset($term) && $term != 'null') {
			$this->data['SearchIndex']['term'] = $term;
			App::import('Core', 'Sanitize');
			$term = Sanitize::escape($term);
			$this->paginate['SearchIndex']['conditions'][] = array(
				'OR' => array(
					"MATCH(SearchIndex.data) AGAINST('$term' IN BOOLEAN MODE)",
					'SearchIndex.data LIKE' => "%{$term}%"));
			$this->paginate['SearchIndex']['fields'] = "*, MATCH(SearchIndex.data) AGAINST('$term' IN BOOLEAN MODE) AS score";
			$this->paginate['SearchIndex']['order'] = "score DESC";
		}

		$results = $this->paginate();
		if (count($results) == 1) {
			$this->redirect(json_decode($results['0']['SearchIndex']['url'], true));
		}

		// Get types for select drop down
		$types = $this->SearchIndex->getTypes();
		$this->set(compact('results', 'term', 'types'));
		$this->pageTitle = 'Search';
	}
}
?>