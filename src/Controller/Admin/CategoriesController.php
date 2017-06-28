<?php
namespace App\Controller\Admin;

use App\Controller\Admin\AppController;

class CategoriesController extends AppController
{
    /**
     * A list of actions that should be allowed for
     * authenticated users
     *
     * @var array
     */
    protected $allowedActions = [
        'index',
        'view',
    ];

    public function index()
    {
        $this->Crud->action()->config('scaffold.actions', []);
        $this->Crud->action()->config('scaffold.fields', [
            'id',
            'name',
            'slug',
            'description',
        ]);
        return $this->Crud->execute();
    }
}
