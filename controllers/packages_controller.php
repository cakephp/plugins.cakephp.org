<?php
class PackagesController extends AppController {

/**
 * The name of this controller. Controller names are plural, named after the model they manipulate.
 *
 * @var string
 * @access public
 * @link http://book.cakephp.org/view/959/Controller-Attributes
 */
    var $name = 'Packages';

/**
 * Array containing the names of components this controller uses. Component names
 * should not contain the "Component" portion of the classname.
 *
 * Example: `var $components = array('Session', 'RequestHandler', 'Acl');`
 *
 * @var array
 * @access public
 * @link http://book.cakephp.org/view/961/components-helpers-and-uses
 */
    var $components = array('Searchable.Search');

/**
 * An array containing the names of helpers this controller uses. The array elements should
 * not contain the "Helper" part of the classname.
 *
 * Example: `var $helpers = array('Html', 'Javascript', 'Time', 'Ajax');`
 *
 * @var mixed A single name as a string or a list of names as an array.
 * @access protected
 * @link http://book.cakephp.org/view/961/components-helpers-and-uses
 */
    var $helpers = array('Searchable.Searchable');

/**
 * Default page for entire application
 */
	function home() {
		$packages = $this->Package->find('latest');
		$this->set(compact('packages'));
	}

/**
 * Index page that also provides search functionality
 *
 * @param string $search String to search by
 * @todo refactor this to use something like Sphinx
 */
	function index($search = null) {
		$seo = null;
		if (isset($this->params['named']['with'])) {
			$seo = $search = Inflector::singularize($this->params['named']['with']);
		}

		$this->paginate = array(
			'index',
			'paginateType' => $search
		);

		$packages = $this->paginate();

		$this->set(compact('packages', 'search'));
	}

/**
 * Allows searching of the SearchIndex
 *
 * @param string $search String to search by
 * @todo Figure out whats the difference between this and the index() action
 */
	function search($search = null) {
		// Redirect with search data in the URL in pretty format
		$this->Search->redirectUnlessGet();

		if (!isset($this->params['term']) || !strlen($this->params['term'])) {
			$this->redirect(array('action' => 'index'));
		}

		// Get Pagination results
		$this->loadModel('Searchable.SearchIndex');
		$packages = $this->Search->paginate($search);

		$this->set(compact('packages', 'search'));
		$this->_seoForAction($search);
		$this->render('index');
	}

/**
 * Allows viewing of a particular package
 *
 * @param string $maintainer Maintainer name
 * @param string $package Package name
 */
	function view($maintainer = null, $package = null) {
		try {
			$package = $this->Package->find('view', array(
				'maintainer' => $maintainer,
				'package' => $package,
			));
		} catch (Exception $e) {
			$this->_flashAndRedirect($e->getMessage());
		}

		$this->set(compact('package'));
	}

/**
 * Provides a jquery autocomplete response
 */
    function autocomplete() {
        $term = (isset($this->params['url']['term'])) ? $this->params['url']['term'] : '';
        $this->set('results', $this->Package->find('autocomplete', array('term' => $term)));
        $this->layout = 'ajax';
        Configure::write('debug', 0);
    }

/**
 * Creates seo information for the particular action
 *
 * @param string $extra Extra string to use in sprintf
 */
	function _seoForAction($extra = null) {
		$slug = $this->Package->seo($this->params);
		if (!$slug) {
			return;
		}
	}

	function _seoHome() {
		$this->Sham->loadBySlug('packages/home');

		$this->Sham->setMeta('title', 'CakePackages: Open source CakePHP Plugins and Applications');
		$this->Sham->setMeta('description', 'CakePHP Package Index - Search for reusable, open source CakePHP plugins and applications, tutorials and code snippets on CakePackages');
		$this->Sham->setMeta('keywords', 'cakephp package, cakephp, plugins, php, open source code, tutorials');
		$this->Sham->setMeta('canonical', '/', array('escape' => false));
	}

	function _seoIndex() {
		$this->Sham->loadBySlug('packages');

		$this->Sham->setMeta('title', 'CakePHP Plugin and Application Search | CakePackages');
		$this->Sham->setMeta('description', 'CakePHP Package Index - Search for reusable, open source CakePHP plugins and applications, tutorials and code snippets');
		$this->Sham->setMeta('keywords', 'package search index, cakephp package, cakephp, plugins, php, open source code, tutorials');
		$this->Sham->setMeta('canonical', '/packages/');
		if (!in_array($this->here, array('/packages', '/packages/'))) {
			$this->Sham->setMeta('robots', 'noindex');
		}
	}

	function _seoView() {
		if (!class_exists('Sanitize')) {
			App::import('Core', 'Sanitize');
		}
		
		$package = $this->viewVars['package'];

		$canonical = 'package/' . $package['Package']['name'] . '/' . $package['Maintainer']['username'];
		$this->Sham->loadBySlug($canonical);

		$title = array();
		$title[] = Sanitize::clean($package['Package']['name'] . ' by ' . $package['Maintainer']['username']);
		$title[] = 'CakePHP Plugins and Applications';
		$title[] = 'CakePackages';
		$description = Sanitize::clean($package['Package']['description']) . ' - CakePHP Package on CakePackages';
		$keywords = explode(' ', $package['Package']['name']);
		if (count($keywords) > 1) {
			$keywords[] = $package['Package']['name'];
		}
		$keywords[] = 'cakephp package';
		$keywords[] = 'cakephp';

		foreach ($this->Package->validTypes as $type) {
			if (isset($package['Package']['contains_' . $type]) && $package['Package']['contains_' . $type] == 1) {
				$keywords[] = $type;
			}
		}

		$this->Sham->setMeta('title', implode(' | ', $title));
		$this->Sham->setMeta('description', $description);
		$this->Sham->setMeta('keywords', implode(', ', $keywords));
		$this->Sham->setMeta('canonical', '/' . $canonical . '/');
	}
}