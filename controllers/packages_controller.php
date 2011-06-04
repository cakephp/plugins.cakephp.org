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
        $this->set(array(
            'latest' => $this->Package->find('latest'),
            'random' => $this->Package->find('random'),
        ));
    }

/**
 * Alternative pagination method for showing latest packages
 */
    function latest() {
        $this->paginate = array('latest', 'is_paginate' => true);
        $packages = $this->paginate();

        $this->set(compact('packages'));
        $this->_seoForAction();
        $this->render('index');
    }

/**
 * Index page that also provides search functionality
 *
 * @param string $search String to search by
 * @todo refactor this to use something like Sphinx
 */
    function index($search = null) {
        $this->paginate = array(
            'index',
            'paginateType' => $search
        );

        $packages = $this->paginate();

        $this->set(compact('packages', 'search'));
        $this->_seoForAction();
    }

/**
 * Filters results by package attributes and paginates the result
 *
 * @todo refactor this into /index
 */
    function filter() {
        $search = Inflector::singularize($this->params['by']);
        $this->paginate = array(
            'index',
            'paginateType' => $search,
        );

        $packages = $this->paginate();

        $this->set(compact('packages', 'search'));
        $this->_seoForAction($search);
        $this->render('index');
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
            $this->set('package', $this->Package->find('view', array(
                'maintainer' => $maintainer,
                'package' => $package,
            )));
        } catch (Exception $e) {
            $this->_flashAndRedirect($e->getMessage());
        }
    }

/**
 * Allows editing a package by id
 *
 * @param string $id package id
 */
    function edit($id = null) {
        $this->_redirectUnless($id, __('Invalid package', true));
        $this->_redirectUnless($this->data, __('Invalid package', true));

        if (!empty($this->data)) {
            if ($this->Package->save($this->data)) {
                $this->_flashAndRedirect(__('The package has been saved', true));
            } else {
                $this->Session->setFlash(__('The package could not be saved. Please, try again.', true));
            }
        }

        if (empty($this->data)) {
            try {
                $this->data = $this->Package->find('edit', $id);
            } catch (Exception $e) {
                $this->_flashAndRedirect($e->getMessage());
            }

            $this->_redirectUnless($this->data);
        }

        $this->set('maintainers', $this->Package->Maintainer->find('list'));
    }

/**
 * Allows deleting of a package
 *
 * @param string $id package id
 * @return void
 * @author Jose Diaz-Gonzalez
 */
    function delete($id = null) {
        $this->_redirectUnless($id);

        $message = __('Package was not deleted', true);
        if ($this->Package->delete($id)) {
            $message = __('Package deleted', true);
        }

        $this->_flashAndRedirect($message);
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
        switch ($this->params['action']) {
            case 'index':
                $h2_for_layout = $title_for_layout = 'Browse Packages';
                break;
            case 'filter':
                $h2_for_layout = sprintf('Browse Packages containing %s', $extra);
                $title_for_layout = $h2_for_layout;
                break;
            case 'search':
                $h2_for_layout = sprintf('Search Results for %s', $extra);
                $title_for_layout = $h2_for_layout;
                break;
            case 'latest':
                $h2_for_layout = $title_for_layout = 'Latest Packages';
                break;
        }

        $this->set(compact('h2_for_layout', 'title_for_layout'));
    }

}