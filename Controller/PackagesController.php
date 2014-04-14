<?php
class PackagesController extends AppController {
/**
 * helpers
 *
 * @var array
 */
	public $helpers = array(
		'Ratings.Rating',
		'DataTable.DataTable',
	);

/**
 * components
 *
 * @var array
 */
	public $components = array(
		'DataTable.DataTable' => array(
			'columns' => array(
				'id',
				'name',
				'Maintainer.username' => 'By',
				'created' => false,
				'last_pushed_at' => false,
				'deleted' => false,
				'repository_url' => false,
				'Actions' => null,
			),
			'triggerAction' => array('admin_index'),
		),
	);

/**
 * _ajax
 *
 * @var array
 */
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
		$packages = $this->Package->find('home');
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
			list($data, $query) = $this->Package->cleanParams($this->request->data, array(
				'allowed' => Package::$_allowedFilters,
				'rinse' => false,
			));
			return $this->redirect(array('?' => $data, 'escape' => false));
		}

		list($this->request->data, $query) = $this->Package->cleanParams(
			$this->request->query, array(
				'allowed' => Package::$_allowedFilters,
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
		$next = $this->Package->getNextPage(array_merge(
			(array) $this->request->query,
			(array) $this->request->data
		), $this->request->params['paging']['Package']['nextPage']);

		$this->request->data['query'] = $query;
		$this->set(compact('next', 'order', 'packages', 'title'));
	}

/**
 * Redirects to the :id-:slug url
 *
 * @param string $maintainer Maintainer name
 * @param string $package Package name
 */
	public function utility_redirect($maintainer = null, $package = null) {
		try {
			$package = $this->Package->find('redirect', compact('maintainer', 'package'));
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage(), 'flash/error');
			return $this->redirect($this->redirectTo);
		}

		return $this->redirect(array(
			'controller' => 'packages', 'action' => 'view',
			'id' => $package['Package']['id'], 'slug' => $package['Package']['name']
		));
	}

/**
 * Allows viewing of a particular package
 *
 * @param string $package_id Package id
 * @param string $slug Package slug
 */
	public function view() {
		if (isset($this->request->params['id'])) {
			$package_id = $this->request->params['id'];
		}

		if (isset($this->request->params['slug'])) {
			$slug = $this->request->params['slug'];
		}

		$user_id = AuthComponent::user('id');

		try {
			$package = $this->Package->find('view', compact('package_id', 'user_id'));
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage(), 'flash/error');
			return $this->redirect($this->redirectTo);
		}

		if ($slug != $package['Package']['name']) {
			return $this->redirect(array(
				'controller' => 'packages', 'action' => 'show',
				'id' => $package['Package']['id'], 'slug' => $package['Package']['name']
			));
		}

		$disqus = $this->Package->disqus($package);
		$this->set(compact('disqus', 'package'));
	}

	public function categories() {
		$categories = $this->Package->Category->find('list', array(
			'fields' => array('slug', 'name'),
			'order' => array('slug'),
		));
		$this->set(compact('categories'));
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
			return $this->redirect($this->referer('/', true));
		}

		$branch = 'master';
		if (!empty($this->request->params['named']['branch'])) {
			$branch = $this->request->params['named']['branch'];
		}

		$download_url = $this->Package->find('download', compact('id', 'branch'));
		if (!$download_url) {
			$this->Session->setFlash('Invalid Package download', 'flash/error');
			return $this->redirect($this->referer('/', true));
		}

		return $this->redirect($download_url);
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
		return $this->redirect($this->referer('/', true));
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
		return $this->redirect($this->referer('/', true));
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
			return $this->redirect($this->referer(array('controller' => 'packages', 'action' => 'suggest'), true));
		}
	}

/**
 * admin_index
 */
	public function admin_index() {
		$this->Package->enableSoftDeletable(array('find'), false);
		$this->paginate = array(
			'Package' => array(
				'contain' => array('Maintainer'),
			),
		);
	}

/**
 * admin_edit
 *
 * @param integer $id
 */
	public function admin_edit($id = null) {
		$this->Package->enableSoftDeletable(array('find'), false);
		if ($this->_isFromForm('Package')) {
			if ($this->Package->save($this->request->data)) {
				$this->Session->setFlash(__('Saved package #%d', $this->Package->id), 'flash/success');
				return $this->redirect(array('action' => 'index'));
			}
		} else {
			$this->Package->contain(array('Maintainer', 'Tag'));
			$this->request->data = $this->Package->findById($id);
		}
		$this->set('categories', $this->Package->categories());
		$this->set('validTypes', $this->Package->_validTypes);
	}

/**
 * disable
 *
 * @param integer $id
 */
	public function admin_disable($id = null) {
		$enabled = $this->Package->enable($id);
		if ($enabled) {
			$this->Session->setFlash(__('Package #%d is now enabled.', $id), 'flash/success');
		} else {
			$this->Session->setFlash(__('Package #%d is now disabled.', $id), 'flash/success');
		}
		return $this->redirect($this->referer());
	}

/**
 * update github info for package(s)
 *
 * @param integer $id
 */
	public function admin_update($id = null) {
		if ($id) {
			if ($this->Package->enqueue('UpdatePackageJob', array($id))) {
				$this->Session->setFlash(__('Package #%d has been queued for updating.', $id), 'flash/success');
			} else {
				$this->Session->setFlash(__('Package #%d could not be queued for updating.', $id), 'flash/error');
			}
		} else {
			$packages = $this->Package->find('list');
			foreach ($packages as $id => $name) {
				$this->Package->enqueue('UpdatePackageJob', array($id));
			}
			$this->Session->setFlash(__('Attempted to queue %d packages.', count($packages)), 'flash/success');
		}
		return $this->redirect(array('admin' => true, 'action' => 'index'));
	}

/**
 * admin_categorize
 *
 * @param integer $id
 */
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
 * Ability to kick off admin jobs
 */
	public function admin_jobs() {
		if ($this->_isFromForm('Package')) {
			try {
				$this->Package->fireJob($this->request->data);
				$this->Session->setFlash(__('Job has been loaded and enqueued.'), 'flash/success');
			} catch (Exception $e) {
				$this->Session->setFlash($e->getMessage(), 'flash/error');
			}
			return $this->redirect($this->referer());
		}
		$this->set('jobs', $this->Package->getJobs());
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
