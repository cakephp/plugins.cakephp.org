<?php
App::uses('AppController', 'Controller');

class MaintainersController extends AppController {

/**
 * Paginates the current maintainers
 *
 * @return void
 */
	public function index() {
		return $this->redirect(array(
			'controller' => 'packages',
			'action' => 'home'
		));
	}

/**
 * Redirects to the :id-:slug url
 *
 * @param string $username Maintainer name
 * @return void
 */
	public function utility_redirect($username = null) {
		try {
			$maintainer = $this->Maintainer->find('redirect', array(
				'username' => $username
			));
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage(), 'flash/error');
			return $this->redirect($this->redirectTo);
		}

		return $this->redirect(array(
			'controller' => 'maintainers', 'action' => 'view',
			'id' => $maintainer['Maintainer']['id'], 'slug' => $maintainer['Maintainer']['username']
		));
	}

/**
 * Allows the viewing of a user
 *
 * @return void
 */
	public function view() {
		$maintainerId = $this->request->param('id');
		$slug = $this->request->param('slug');

		try {
			$maintainer = $this->Maintainer->find('view', array(
				'maintainer_id' => $maintainerId
			));
		} catch (Exception $e) {
			$this->Session->setFlash($e->getMessage(), 'flash/error');
			return $this->redirect($this->redirectTo);
		}

		if ($slug != $maintainer['Maintainer']['username']) {
			return $this->redirect(array(
				'controller' => 'maintainers', 'action' => 'view',
				'id' => $maintainer['Maintainer']['id'], 'slug' => $maintainer['Maintainer']['username']
			));
		}

		$this->set(compact('maintainer'));
	}

/**
 * Sets seo information for the index page
 *
 * @return void
 */
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

/**
 * Sets seo information for the view page
 *
 * @return void
 */
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
