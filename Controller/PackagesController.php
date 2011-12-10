<?php
class PackagesController extends AppController {

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
	public function index() {
		if ($this->request->is('post')) {
			$clean = $this->Package->cleanParams($this->request->data, false);
			$this->redirect($clean);
		}

		$allowed = array('with', 'since', 'query', 'watchers');
		$this->request->data = $this->Package->cleanParams($this->request->params['named'], compact('allowed'));
		$this->paginate = array(
			'index',
			'named' => $this->request->data,
			'limit' => 21,
		);

		$packages = $this->paginate();

		$tabs = $this->Package->tabs;
		$merge = $this->Package->cleanParams($this->request->data, false);
		$this->set(compact('merge', 'packages', 'tabs'));
	}

/**
 * Allows viewing of a particular package
 *
 * @param string $maintainer Maintainer name
 * @param string $package Package name
 */
	public function view($maintainer = null, $package = null) {
		try {
			$package = $this->Package->find('view', compact('maintainer', 'package'));
		} catch (Exception $e) {
			$this->_flashAndRedirect($e->getMessage());
		}

		$this->set(compact('package'));
	}

	public function suggest() {
		if (!empty($this->request->data['Package'])) {
			if ($this->Package->suggest($this->request->data['Package'])) {
				$this->Session->setFlash('Thanks, your submission will be reviewed shortly!', 'flash/success');
				unset($this->request->data['Package']);
			} else{
				$this->Session->setFlash('There was some sort of error...', 'flash/error');
			}
		}
	}

/**
 * Provides a jquery autocomplete response
 */
	public function autocomplete() {
		$term = (isset($this->request->params['url']['term'])) ? $this->request->params['url']['term'] : '';
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
		if (!in_array($this->request->here, array('/packages', '/packages/'))) {
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
		$package = $this->viewVars['package'];
		$canonical = 'package/' . $package['Package']['name'] . '/' . $package['Maintainer']['username'];
		$this->Sham->loadBySlug($canonical);
		list($title, $description, $keywords) = $this->Package->seoView($package);

		$this->Sham->setMeta('title', $title);
		$this->Sham->setMeta('description', $description);
		$this->Sham->setMeta('keywords', $keywords);
		$this->Sham->setMeta('canonical', '/' . $canonical . '/', array('escape' => false));
	}

}