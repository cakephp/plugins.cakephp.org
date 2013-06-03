<?php
class MaintainersController extends AppController {

/**
 * Paginates the current maintainers
 */
	public function index() {
		$this->paginate = array('index');
		$maintainers = $this->paginate();
		$this->set(compact('maintainers'));
	}

/**
 * Allows the viewing of a user
 *
 * @param string $username Username
 */
	public function view($username = null) {
		try {
			$this->set('maintainer', $maintainer = $this->Maintainer->find('view', $username));
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage(), 'flash/error');
			$this->redirect($this->redirectTo);
		}
	}

	public function _seoIndex() {
		$keywords = array();
		$keywords[] = 'cakephp developers';
		$keywords[] = 'package maintainers';
		$keywords[] = 'cakephp package';
		$keywords[] = 'cakephp';

		$this->Sham->setMeta('title', 'CakePHP Maintainer Index | CakePackages');
		$this->Sham->setMeta('description', 'CakePHP Maintainer Index - Browse CakePHP application and plugin developers');
		$this->Sham->setMeta('keywords', implode(', ', $keywords));
		$this->Sham->setMeta('canonical', '/');
	}

	public function _seoView() {
		$maintainer = $this->viewVars['maintainer'];
		$canonical = 'maintainer/' . $maintainer['Maintainer']['username'];
		$this->Sham->loadBySlug($canonical);
		list($title, $description, $keywords) = $this->Maintainer->seoView($maintainer);

		$this->Sham->setMeta('title', $title);
		$this->Sham->setMeta('description', $description);
		$this->Sham->setMeta('keywords', $keywords);
		$this->Sham->setMeta('canonical', '/' . $canonical . '/', array('escape' => false));
	}

}
