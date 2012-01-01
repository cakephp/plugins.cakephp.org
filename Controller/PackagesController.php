<?php
class PackagesController extends AppController {

	public $helpers = array('Ratings.Rating');

/**
 * Default page for entire application
 */
	public function home() {
		$sortClass = null;
		if (empty($this->request->params['named']['sort'])) {
			$sortClass = 'class="ui-tabs-selected"';
		}
		$packages = $this->Package->find('latest');
		$this->set(compact('packages', 'sortClass'));
	}

/**
 * Index page that also provides search functionality
 *
 * @param string $search String to search by
 * @todo refactor this to use something like Sphinx
 */
	public function index() {
		if ($this->request->is('post')) {
			list($data, $query) = $this->Package->cleanParams($this->request->data, array(
				'rinse' => false,
				'allowed' => $this->Package->allowedFilters,
			));
			$this->redirect($data);
		}

		list($this->request->data, $query) = $this->Package->cleanParams(
			$this->request->params['named'], array(
				'allowed' => $this->Package->allowedFilters,
				'coalesce' => true,
			)
		);
		$this->paginate = array(
			'index',
			'limit' => 20,
			'named' => $this->request->data,
		);
		$this->request->data['query'] = $query;

		$packages = $this->paginate();
		$tabs = $this->Package->tabs;
		$this->set(compact('packages', 'tabs'));
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

		$disqus = $this->Package->disqus($package);
		$this->set(compact('disqus', 'package'));
	}

/**
 * Redirects to proper download url
 *
 * @param int $id 
 * @todo Track downloads for packages
 */
	public function download($id = null) {
		if (!$id) {
			$this->Session->setFlash('Invalid Package download');
			$this->redirect($this->referer('/', true));
		}

		$branch = 'master';
		if (!empty($this->request->params['named']['branch'])) {
			$branch = $this->request->params['named']['branch'];
		}

		$download_url = $this->Package->find('download', compact('id', 'branch'));
		if (!$download_url) {
			$this->Session->setFlash('Invalid Package download');
			$this->redirect($this->referer('/', true));
		}

		$this->redirect($download_url);
	}

/**
 * This action takes the rating of an package and processes it
 *
 * @param string $id video id
 * @param string "up" or "down"
 * @return void
 * @access public
 */
	public function rate($id = null, $direction = null) {
		$status = 400;
		$message = __d('packages', 'Unable to vote on this package');
		if ($this->Package->ratePackage($id, $this->Auth->user('id'), $direction)) {
			$status = 200;
			$message = __d('packages', 'Your vote was successfully recorded.');
		}

		if ($this->RequestHandler->prefers('json')) {
			$this->RequestHandler->renderAs($this, 'json');
			$this->set(compact('message', 'status'));
		} else {
			$this->Session->setFlash($message, 'flash/' . ($status == 200 ? 'success' : 'error'));
			$this->redirect($this->referer('/', true));
		}
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
		$canonical = 'package/' . $package['Maintainer']['username'] . '/' . $package['Package']['name'];
		$this->Sham->loadBySlug($canonical);
		list($title, $description, $keywords) = $this->Package->seoView($package);

		$this->Sham->setMeta('title', $title);
		$this->Sham->setMeta('description', $description);
		$this->Sham->setMeta('keywords', $keywords);
		$this->Sham->setMeta('canonical', '/' . $canonical . '/', array('escape' => false));
	}

}