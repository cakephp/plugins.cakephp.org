<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Packages Controller
 *
 * @property \App\Model\Table\PackagesTable $Packages
 */
class PackagesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Packages
            ->find('search', search: $this->request->getQueryParams());
        $packages = $this->paginate($query);

        $this->set(compact('packages'));
    }
}
