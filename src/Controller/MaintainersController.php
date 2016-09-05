<?php
namespace App\Controller;

use App\Controller\AppController;

class MaintainersController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Maintainers');
        $this->loadModel('Packages');
    }

    /**
     * Paginates the current maintainers
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        return $this->redirect([
            'controller' => 'packages',
            'action' => 'home'
        ]);
    }

    /**
     * Handles packages/view
     *
     * @return void
     */
    public function view()
    {
        $maintainerId = $this->request->param('id');
        $slug = $this->request->param('slug');

        $maintainer = $this->Maintainers->find('view', [
            'maintainer_id' => $maintainerId,
            'slug' => $slug,
        ])->firstOrFail();

        $packages = $this->Packages
                         ->find('package')
                         ->where(['maintainer_id' => $maintainerId]);

         $this->set('maintainer', $maintainer);
         $this->set('packages', $packages);
    }
}
