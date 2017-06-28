<?php
namespace App\Controller\Admin;

use App\Controller\Admin\AppController;
use Cake\Routing\Router;
use CrudView\BreadCrumb\ActiveBreadCrumb;
use CrudView\BreadCrumb\BreadCrumb;

class PackagesController extends AppController
{
    /**
     * A list of actions that should be allowed for
     * authenticated users
     *
     * @var array
     */
    protected $allowedActions = [
        'index',
    ];

    public function index()
    {
        $fields = [
            'id',
            'maintainer_id',
            'name',
            'repository_url',
            'tags',
            'category_id'
        ];
        if ($this->request->getParam('_ext') === 'csv') {
            $this->set('_serialize', ['packages']);
            $this->set('_extract', $fields);
        }

        $this->Crud->action()->config('scaffold.actions', []);
        $this->Crud->action()->config('scaffold.index_finder_scopes', [
            [
                'title' => __('All'),
                'finder' => 'all',
            ],
            [
                'title' => __('Uncategorized'),
                'finder' => 'uncategorized',
            ],
            [
                'title' => __('No version set'),
                'finder' => 'unversioned',
            ],
        ]);

        if (in_array($this->request->query('finder'), ['uncategorized', 'unversioned'])) {
            $this->Crud->action()->config('findMethod', $this->request->query('finder'));
        }

        $this->Crud->action()->config('scaffold.index_formats', [
            [
                'title' => 'CSV',
                'url' => ['_ext' => 'csv', '?' => $this->request->query]
            ],
            [
                'title' => 'JSON',
                'url' => ['_ext' => 'json', '?' => $this->request->query]
            ],
            [
                'title' => 'XML',
                'url' => ['_ext' => 'xml', '?' => $this->request->query]
            ],
        ]);
        $this->Crud->action()->config('scaffold.fields', $fields);
        return $this->Crud->execute();
    }
}
