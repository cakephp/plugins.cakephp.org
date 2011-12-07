<?php
class MaintainersController extends AppController {

/**
 * The name of this controller. Controller names are plural, named after the model they manipulate.
 *
 * @var string
 * @link http://book.cakephp.org/view/959/Controller-Attributes
 */
	public $name = 'Maintainers';

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
			$this->_flashAndRedirect($e->getMessage());
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
		if (!class_exists('Sanitize')) {
			App::import('Core', 'Sanitize');
		}
		$maintainer = $this->viewVars['maintainer'];

		$canonical = 'maintainer/' . $maintainer['Maintainer']['username'];
		$this->Sham->loadBySlug($canonical);

		if ($maintainer['Maintainer']['name']) {
			$name = $maintainer['Maintainer']['name'];
		} else {
			$name = $maintainer['Maintainer']['username'];
		}

		$title = array();
		$title[] = Sanitize::clean($name);
		$title[] = 'CakePHP Package Maintainer';
		$title[] = 'CakePackages';
		$description = Sanitize::clean($name) . ' - CakePHP Package on CakePackages';

		$keywords = array();
		if (!empty($maintainer['Package'])) {
			$keywords = array_slice(Set::classicExtract($maintainer, 'Package.{n}.name'), 0, 5);
		}
		$keywords[] = 'cakephp package';
		$keywords[] = 'cakephp';

		$this->Sham->setMeta('title', implode(' | ', $title));
		$this->Sham->setMeta('description', $description);
		$this->Sham->setMeta('keywords', implode(', ', $keywords));
		$this->Sham->setMeta('canonical', '/' . $canonical . '/');
	}

}