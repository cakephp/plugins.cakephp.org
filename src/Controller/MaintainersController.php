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
     * Handles packages/view
     *
     * @return void
     */
    public function view()
    {
        $maintainerId = $this->request->getParam('id');
        $slug = $this->request->getParam('slug');

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
