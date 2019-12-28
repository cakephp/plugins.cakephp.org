<?php
namespace App\Controller\Admin;

use App\Controller\Admin\AppController;
use Cake\Event\Event;
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
        'toggleFeature',
    ];

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
        $this->loadComponent('Search.Prg', [
            'actions' => [
                'index',
            ],
            'queryStringWhitelist' => [
                'direction',
                'finder',
                'limit',
                'sort',
            ],
        ]);
        $this->Crud->mapAction('toggleFeature', [
            'className' => 'Crud.Bulk/Toggle',
            'field' => 'featured',
        ]);
    }

    public function index()
    {
        $fields = [
            'id',
            'maintainer_id',
            'name',
            'repository_url',
            'deleted',
            'tags' => [
                'formatter' => function ($name, $value) {
                    return implode(' ', explode(',', $value));
                },
            ],
            'category_id',
            'featured' => [
                'formatter' => function ($name, $value, $entity, $options, $View) {
                    $title = $value ? __('Unfeature') : __('Feature');
                    return $View->Html->link($title, ['action' => 'toggleFeature', $entity->id], ['class' => 'btn btn-primary']);
                },
            ],
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
                'title' => __('Featured'),
                'finder' => 'featured',
            ],
            [
                'title' => __('Uncategorized'),
                'finder' => 'uncategorized',
            ],
            [
                'title' => __('No version set'),
                'finder' => 'unversioned',
            ],
            [
                'title' => __('Deleted'),
                'finder' => 'deleted',
            ],
        ]);

        if (in_array($this->request->query('finder'), ['featured', 'uncategorized', 'unversioned', 'deleted'])) {
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

        $this->Crud->addListener('search', 'Crud.Search', [
            'collection' => 'admin',
        ]);

        $this->Crud->addListener('viewSearch', 'CrudView.ViewSearch', [
            'enabled' => true,
            'autocomplete' => false,
            'selectize' => true,
            'collection' => 'admin',
        ]);

        return $this->Crud->execute();
    }

    public function toggleFeature($id)
    {
        $this->Crud->on('beforeHandle', function (Event $event) use ($id) {
            $this->request = $this->request->withData('id', [$id]);
        });

        $this->Crud->on('beforeRedirect', function (Event $event) {
            $event->subject->url = $this->request->referer();
        });

        return $this->Crud->execute();
    }
}
