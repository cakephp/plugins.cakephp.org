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
            ->find('search', search: $this->request->getQueryParams())
            ->contain(['Tags']);
        $packages = $this->paginate($query);

        $tags = $this->Packages->Tags->find('list', ...['keyField' => 'slug']);

        $this->set(compact('packages', 'tags'));
    }
}
