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
        'toggleDelete',
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
        $this->Crud->mapAction('toggleDelete', [
            'className' => 'Crud.Bulk/Toggle',
            'field' => 'deleted',
        ]);
    }

    public function index()
    {
        $fields = [
            'id',
            'maintainer_id',
            'name',
            'repository_url' => [
                'formatter' => function ($name, $value, $entity, $options, $View) {
                    return $View->Html->link($value, $value, ['target' => '_blank']);
                },
            ],
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
            'deleted' => [
                'formatter' => function ($name, $value, $entity, $options, $View) {
                    $title = $value ? __('Undelete') : __('Delete');
                    return $View->Html->link($title, ['action' => 'toggleDelete', $entity->id], ['class' => 'btn btn-warning']);
                },
            ],
        ];
        if ($this->request->getParam('_ext') === 'csv') {
            $this->set('_serialize', ['packages']);
            $this->set('_extract', $fields);
        }

        $indexFinderScopes = [
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
                'title' => __('Cake 1.3'),
                'finder' => '13',
            ],
            [
                'title' => __('Cake 2'),
                'finder' => '2',
            ],
            [
                'title' => __('Cake 3'),
                'finder' => '3',
            ],
            [
                'title' => __('Cake 4'),
                'finder' => '4',
            ],
            [
                'title' => __('Deleted'),
                'finder' => 'deleted',
            ],
        ];

        $this->Crud->action()->config('scaffold.actions', []);
        $this->Crud->action()->config('scaffold.index_finder_scopes', $indexFinderScopes);


        $allowedFinderMethods = array_map(function($e) {
            return $e['finder'];
        }, $indexFinderScopes);
        if (in_array($this->request->query('finder'), $allowedFinderMethods)) {
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
        return $this->toggle($id);
    }

    public function toggleDelete($id)
    {
        return $this->toggle($id);
    }

    protected function toggle($id)
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
