<?php
class PackagesController extends AppController {

/**
 * The name of this controller. Controller names are plural, named after the model they manipulate.
 *
 * @var string
 * @access public
 * @link http://book.cakephp.org/view/959/Controller-Attributes
 */
	public $name = 'Packages';

/**
 * Default page for entire application
 */
	public function home() {
		$packages = $this->Package->find('latest');
		$this->set(compact('packages'));
	}

/**
 * Index page that also provides search functionality
 *
 * @param string $search String to search by
 * @todo refactor this to use something like Sphinx
 */
	public function index($search = null) {
		if (!empty($this->data)) {
			$clean = $this->Package->cleanParams($this->data, false);
			$this->redirect($clean);
		}

		$allowed = array('with', 'since', 'query', 'watchers');
		$clean = $this->Package->cleanParams($this->params['named'], compact('allowed'));
		$this->paginate = array(
			'index',
			'named' => $clean,
			'limit' => 21
		);

		$packages = $this->paginate();

		$search = null;
		$this->data = $clean;
		$merge = $this->Package->cleanParams($clean, false);
		$this->set(compact('merge', 'packages', 'search'));
	}

/**
 * Allows viewing of a particular package
 *
 * @param string $maintainer Maintainer name
 * @param string $package Package name
 */
	public function view($maintainer = null, $package = null) {
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

	public function suggest() {
		if (!empty($this->data['Package'])) {
			if ($this->Package->suggest($this->data['Package'])) {
				$this->Session->setFlash('Thanks, your submission will be reviewed shortly!', 'flash/success');
				unset($this->data['Package']);
			} else{
				$this->Session->setFlash('There was some sort of error...', 'flash/error');
			}
		}
	}

/**
 * Provides a jquery autocomplete response
 */
	public function autocomplete() {
		$term = (isset($this->params['url']['term'])) ? $this->params['url']['term'] : '';
		$this->set('results', $this->Package->find('autocomplete', array('term' => $term)));
		$this->layout = 'ajax';
		Configure::write('debug', 0);
	}

/**
 * Sets seo information for the homepage
 */
	public function _seoHome() {
		$this->Sham->loadBySlug('packages/home');

		$this->Sham->setMeta('title', 'CakePackages: Open source CakePHP Plugins and Applications');
		$this->Sham->setMeta('description', 'CakePHP Package Index - Search for reusable, open source CakePHP plugins and applications, tutorials and code snippets on CakePackages');
		$this->Sham->setMeta('keywords', 'cakephp package, cakephp, plugins, php, open source code, tutorials');
		$this->Sham->setMeta('canonical', '/', array('escape' => false));
	}

/**
 * Sets SEO information for any of the package search pages
 */
	public function _seoIndex() {
		$this->Sham->loadBySlug('packages');

		$this->Sham->setMeta('title', 'CakePHP Plugin and Application Search | CakePackages');
		$this->Sham->setMeta('description', 'CakePHP Package Index - Search for reusable, open source CakePHP plugins and applications, tutorials and code snippets');
		$this->Sham->setMeta('keywords', 'package search index, cakephp package, cakephp, plugins, php, open source code, tutorials');
		$this->Sham->setMeta('canonical', '/packages/', array('escape' => false));
		if (!in_array($this->here, array('/packages', '/packages/'))) {
			$this->Sham->setMeta('robots', 'noindex, follow');
		}
	}

/**
 * Sets seo information for the suggest page
 */
	public function _seoSuggest() {
		$this->Sham->loadBySlug('packages/suggest');

		$this->Sham->setMeta('title', 'Suggest New Plugins | CakePHP Plugins and Applications | CakePackages');
		$this->Sham->setMeta('description', 'CakePHP Package Suggestion page - Suggest new, open source CakePHP plugins and applications for indexing on CakePackages');
		$this->Sham->setMeta('keywords', 'suggest plugins, cakephp package, cakephp, plugins, php, open source code, tutorials');
		$this->Sham->setMeta('canonical', '/suggest/', array('escape' => false));
	}

/**
 * Sets SEO information for a specific package page
 */
	public function _seoView() {
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
		$this->Sham->setMeta('canonical', '/' . $canonical . '/', array('escape' => false));
	}

}