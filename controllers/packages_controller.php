<?php
class PackagesController extends AppController {
    var $name = 'Packages';
    var $components = array('Searchable.Search');
    var $helpers = array('Searchable.Searchable');

    function home() {
        $latest = $this->Package->find('latest');
        $random = $this->Package->find('random');
        $this->set(compact('latest', 'random'));
    }

    function latest() {
        $this->paginate = array('latest');
        $this->set('packages', $this->paginate());
        $this->render('index');
    }

    function index($search = null) {
        $this->paginate = array(
            'index',
            'paginateType' => $search
        );

        $packages = $this->paginate();

        $this->set(compact('packages', 'search'));
        $this->_seoForAction('index');
    }

    function filter() {
        $search = Inflector::singularize($this->params['by']);
        $this->paginate = array(
            'index',
            'paginateType' => $search,
        );

        $packages = $this->paginate();

        $this->set(compact('packages', 'search'));
        $this->_seoForAction('filter', $search);
        $this->render('index');
    }

    function search($search = null) {
        // Redirect with search data in the URL in pretty format
        $this->Search->redirectUnlessGet();

        // Get Pagination results
        $this->loadModel('Searchable.SearchIndex');
        $packages = $this->Search->paginate($search);

        $this->set(compact('packages', 'search'));
        $this->_seoForAction('search', $search);
        $this->render('index');
    }

    function view($maintainer = null, $package = null) {
        try {
            $this->set('package', $this->Package->find('view', array(
                'maintainer' => $maintainer,
                'package' => $package,
            )));
        } catch (Exception $e) {
            $this->flashAndRedirect($e->getMessage());
        }
    }

    function edit($id = null) {
        if (!$id && empty($this->data)) {
            $this->flashAndRedirect(__('Invalid package', true));
        }
        if (!empty($this->data)) {
            if ($this->Package->save($this->data)) {
                $this->flashAndRedirect(__('The package has been saved', true));
            } else {
                $this->Session->setFlash(__('The package could not be saved. Please, try again.', true));
            }
        }

        if (empty($this->data)) {
            try {
                $this->data = $this->Package->find('edit', $id);
            } catch (Exception $e) {
                $this->flashAndRedirect($e->getMessage());
            }
            $this->redirectUnless($this->data);
        }

        $this->set('maintainers', $this->Package->Maintainer->find('list'));
    }

    function delete($id = null) {
        $this->redirectUnless($id);

        if ($this->Package->delete($id)) {
            $this->Session->setFlash(sprintf(__('%s deleted', true), 'Package'));
        } else {
            $this->Session->setFlash(sprintf(__('%s was not deleted', true), 'Package'));
        }

        $this->redirect(array('action' => 'index'));
    }

    function autocomplete() {
        $term = (isset($this->params['url']['term'])) ? $this->params['url']['term'] : '';
        $this->set('results', $this->Package->find('autocomplete', array('term' => $term)));
        $this->layout = 'ajax';
        Configure::write('debug', 0);
    }

    function _seoForAction($type = 'index', $extra = null) {
        if ($type == 'index') {
            $h2_for_layout = $title_for_layout = 'Browse Packages';
        }
        else if ($type == 'filter') {
            $h2_for_layout = sprintf('Browse Packages containing %ss', $extra);
            $title_for_layout = $h2_for_layout;
        }
        else if ($type == 'search') {
            $h2_for_layout = sprintf('Search Results for %s', $extra);
            $title_for_layout = $h2_for_layout;
        }
        $this->set(compact('h2_for_layout', 'title_for_layout'));
    }

}