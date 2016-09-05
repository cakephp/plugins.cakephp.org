<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Form\SearchForm;
use App\Form\SuggestForm;

class PackagesController extends AppController
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Prg', [
            'allowedFilters' => [
                'collaborators', 'contains', 'contributors',
                'direction', 'forks', 'has', 'open_issues',
                'query', 'sort', 'since', 'watchers', 'with',
                'category', 'version',
            ],
        ]);
    }

    /**
     * Handles packages/index
     */
    public function index()
    {
        if ($this->request->is('post')) {
            return $this->Prg->redirectPost();
        }

        list($this->request->data, $query) = $this->Prg->cleanParams(
            $this->request->query,
            ['coalesce' => true]
        );

        $category = null;
        if (!empty($this->request->data['category'])) {
            $category = $this->Packages->Categories->find('view', [
                'slug' => $this->request->data('category'),
            ])->firstOrFail();
        }

        $search = new SearchForm();
        // $packages = $this->paginate($this->Packages->find('index', $this->request->data));
        $packages = $this->Packages->find('index', $this->request->data)->all();
        $this->set([
            'category' => $category,
            'packages' => $packages,
            'search' => $search,
        ]);
    }

    /**
     * Handles packages/home
     */
    public function home()
    {
        $search = new SearchForm();
        $suggest = new SuggestForm();
        $this->set(['search' => $search, 'suggest' => $suggest]);
    }

    /**
     * Handles packages/view
     */
    public function view()
    {
        $package = $this->Packages->find('view', [
            'package_id' => $this->request->param('id'),
            'slug' => $this->request->param('slug'),
            'user_id' => $this->Auth->user('id'),
        ])->firstOrFail();

        $this->set('package', $package);
    }
}
