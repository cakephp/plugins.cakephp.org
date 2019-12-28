<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Form\SearchForm;
use App\Form\SuggestForm;
use Josegonzalez\CakeQueuesadilla\Traits\QueueTrait;

class PackagesController extends AppController
{
    use QueueTrait;

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
        $this->loadComponent('PersistErrors');
        $this->loadComponent('Prg', [
            'allowedFilters' => [
                'category',
                'collaborators',
                'contributors',
                'direction',
                'forks',
                'has',
                'keyword',
                'open_issues',
                'query',
                'sort',
                'since',
                'watchers',
                'with',
                'version',
            ],
        ]);
    }

    /**
     * Handles packages/index
     *
     * @return void
     */
    public function index()
    {
        if ($this->request->is('post')) {
            return $this->Prg->redirectPost();
        }

        list($this->request->data, $query) = $this->Prg->cleanParams(
            $this->request->getQuery(),
            ['coalesce' => true]
        );

        $category = null;
        if (!empty($this->request->getData('category'))) {
            $category = $this->Packages->Categories->find('view', [
                'slug' => $this->request->getData('category'),
            ])->firstOrFail();
        }

        $searchForm = new SearchForm();
        $packages = $this->paginate($this->Packages->find('index', $this->request->getData()));

        $this->request = $this->request->withData('query', $query);
        $this->set([
            'category' => $category,
            'packages' => $packages,
            'searchForm' => $searchForm,
        ]);
    }

    /**
     * Handles packages/home
     *
     * @return void
     */
    public function home()
    {
        $searchForm = new SearchForm();
        $suggestForm = new SuggestForm();
        $this->PersistErrors->apply($suggestForm);
        $this->set([
            'searchForm' => $searchForm,
            'suggestForm' => $suggestForm,
        ]);
    }

    /**
     * Handles packages/view
     *
     * @return void
     */
    public function view()
    {
        $searchForm = new SearchForm();
        $package = $this->Packages->find('view', [
            'package_id' => $this->request->getParam('id'),
            'slug' => $this->request->getParam('slug'),
            'user_id' => $this->Auth->user('id'),
        ])->firstOrFail();

        $this->push(['\App\Job\CloneJob', 'perform'], [
            'package_id' => $this->request->getParam('id'),
        ]);
        $this->push(['\App\Job\ClassifyJob', 'perform'], [
            'package_id' => $this->request->getParam('id'),
        ]);

        $this->set('package', $package);
        $this->set('searchForm', $searchForm);
    }

    /**
     * This action allows users to suggest new packages for inclusion on the site
     *
     * @return void
     */
    public function suggest()
    {
        if (!$this->request->is(['post', 'put'])) {
            $redirectUrl = $this->referer(['controller' => 'packages', 'action' => 'suggest'], true);
            return $this->redirect($redirectUrl);
        }

        $suggestForm = new SuggestForm();
        if ($suggestForm->execute($this->request->getData())) {
            $this->Flash->success(__('Thanks, your submission will be reviewed shortly!'));
        } else {
            $this->PersistErrors->persist($suggestForm);
            $this->Flash->error(__('There was some sort of error...'));
        }
        return $this->redirect($this->referer(null, true));
    }
}
