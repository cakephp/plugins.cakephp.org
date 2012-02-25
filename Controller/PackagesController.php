<?php
class PackagesController extends AppController {

	public $helpers = array(
		'Ratings.Rating',
	);

	public $_ajax = array(
		'bookmark',
		'home',
		'index',
		'like',
		'suggest',
	);

/**
 * Default page for entire application
 */
	public function home() {
		return $this->setAction('index');
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
				'allowed' => $this->Package->_allowedFilters,
			));
			$this->redirect(array('?' => $data, 'escape' => false));
		}

		list($this->request->data, $query) = $this->Package->cleanParams(
			$this->request->query, array(
				'allowed' => $this->Package->_allowedFilters,
				'coalesce' => true,
			)
		);

		$this->paginate = array(
			'paramType' => 'querystring',
			'type' => 'index',
			'limit' => 20,
			'named' => $this->request->data
		);

		$order = $this->Package->_findIndex('before', $this->paginate);
		$order = $order['order'][0][0];

		$packages = $this->paginate();
		$count = $this->Package->find('count');
		$next = $this->Package->getNextPage(array_merge(
			(array) $this->request->query,
			(array) $this->request->data
		), $this->request->params['paging']['Package']['nextPage']);

		$this->request->data['query'] = $query;
		$this->set(compact('count', 'next', 'order', 'packages', 'title'));
	}

/**
 * Allows viewing of a particular package
 *
 * @param string $maintainer Maintainer name
 * @param string $package Package name
 */
	public function view($maintainer = null, $package = null) {
		try {
			$user_id = AuthComponent::user('id');
			$package = $this->Package->find('view', compact('maintainer', 'package', 'user_id'));
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage(), 'flash/error');
			$this->redirect($this->redirectTo);
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
			$this->Session->setFlash('Invalid Package download', 'flash/error');
			$this->redirect($this->referer('/', true));
		}

		$branch = 'master';
		if (!empty($this->request->params['named']['branch'])) {
			$branch = $this->request->params['named']['branch'];
		}

		$download_url = $this->Package->find('download', compact('id', 'branch'));
		if (!$download_url) {
			$this->Session->setFlash('Invalid Package download', 'flash/error');
			$this->redirect($this->referer('/', true));
		}

		$this->redirect($download_url);
	}

/**
 * This action takes likes/dislikes a package for the currently logged in user
 *
 * @param string $id package id
 * @return void
 */
	public function like($id = null) {
		try {
			$result = $this->Package->ratePackage($id, $this->Auth->user('id'), 'like');
			$status = 200;
			if ($result) {
				$message = __d('packages', 'Thanks for liking this package.');
			} else {
				$message = __d('packages', 'Package preference removed.');
			}
		} catch (Exception $e) {
			$status = $e->getCode();
			$message = $e->getMessage();
		}

		$this->Session->setFlash($message, 'flash/' . ($status == 200 ? 'success' : ($status >= 600 ? 'info' : 'error')));
		$this->redirect($this->referer('/', true));
	}

/**
 * This action bookmarks/unbookmarks a package for the currently logged in user
 *
 * @param int $id package id
 * @return void
 */
	public function bookmark($id = null) {
		try {
			$result = $this->Package->favoritePackage($id, $this->Auth->user('id'), 'bookmark');
			$status = 200;
			if ($result) {
				$message = __d('packages', 'Package bookmarked.');
			} else {
				$message = __d('packages', 'Bookmark removed.');
			}
		} catch (Exception $e) {
			$status = $e->getCode();
			$message = $e->getMessage();
		}

		$this->Session->setFlash($message, 'flash/' . ($status == 200 ? 'success' : ($status >= 600 ? 'info' : 'error')));
		$this->redirect($this->referer('/', true));
	}

	public function suggest() {
		if ($this->_isFromForm('Package')) {
			$result = $this->Package->suggest($this->request->data['Package']);
			if (!$result) {
				return $this->Session->setFlash('There was some sort of error...', 'flash/error');
			}

			$this->Session->setFlash(
				__('Thanks, your submission of <i>%s/%s</i> will be reviewed shortly!', $result[0], $result[1]
			), 'flash/success');
			$this->redirect($this->referer(array('controller' => 'packages', 'action' => 'suggest'), true));
		}
	}

	public function admin_categorize($id = null) {
		$user_id = $this->Auth->user('id');

		if ($this->_isFromForm('Package')) {
			try {
				$id = $this->Package->categorizePackage($this->request->data);
				$this->Session->setFlash(__('Categorized package #%d', $id), 'flash/success');
			} catch (Exception $e) {
				$this->Session->setFlash($e->getMessage(), 'flash/error');
			}
		}

		$categories = $this->Package->categories($user_id);
		try {
			$package = $this->Package->find('uncategorized', compact('id', 'user_id'));
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage(), 'flash/error');
		}
		$this->set(compact('categories', 'package'));
	}

/**
 * Sets SEO information for any
 * of the package search pages,
 * as well as the home page
 */
	public function _seoIndex() {
		if ($this->_originalAction == 'home') {
			$this->Sham->loadBySlug('packages/home');
			$title = __('Latest CakePHP Packages');
			$this->Sham->setMeta('title', 'CakePackages: Open source CakePHP Plugins and Applications');
			$this->Sham->setMeta('keywords', 'cakephp package, cakephp, plugins, php, open source code, tutorials');
			$this->Sham->setMeta('canonical', '/', array('escape' => false));
		} else {
			$this->Sham->loadBySlug('packages');
			$title = __('Available CakePHP packages');

			if (!empty($this->request->data['query'])) {
				$title = 'Results for <span>' . $this->request->data['query'] . '</span>';
				$this->Sham->setMeta('title', 'CakePHP Plugin and Application Search | CakePackages');
				$this->Sham->setMeta('keywords', 'package search index, cakephp package, cakephp, plugins, php, open source code, tutorials');
			} else {
				$this->Sham->setMeta('title', 'CakePackages: Open source CakePHP Plugins and Applications');
				$this->Sham->setMeta('keywords', 'cakephp package, cakephp, plugins, php, open source code, tutorials');
			}

			$this->Sham->setMeta('canonical', '/packages/', array('escape' => false));
			if (!in_array($this->request->here, array('/packages', '/packages/'))) {
				$this->Sham->setMeta('robots', 'noindex, follow');
			}

			$this->Sham->setMeta('description', 'CakePHP Package Index - Search for reusable, open source CakePHP plugins and applications, tutorials and code snippets on CakePackages');
		}

		$this->set(compact('title'));
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