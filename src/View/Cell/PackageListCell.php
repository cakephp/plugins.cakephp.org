<?php
namespace App\View\Cell;

use Cake\View\Cell;

class PackageListCell extends Cell
{
    public function featured()
    {
        $this->set('title', 'Featured Packages');
        $this->set(['disableAdmin' => true]);
        $this->loadModel('Packages');
        $ids = $this->Packages->find('list')
            ->where(['Packages.deleted' => false])
            ->where(['Packages.featured' => true])
            ->orderDesc('Packages.watchers')
            ->limit(8)
            ->toArray();
        return $this->display(array_keys($ids));
    }

    public function popular()
    {
        $this->set('title', 'Popular Packages');
        $this->set(['disableAdmin' => true]);
        $this->loadModel('Packages');
        $ids = $this->Packages->find('list')
            ->where(['Packages.deleted' => false])
            ->where(['Packages.tags LIKE' => '%version:3%'])
            ->orderDesc('Packages.watchers')
            ->limit(8)
            ->toArray();
        return $this->display(array_keys($ids));
    }


    protected function display(array $packageIds = [])
    {
        $this->viewBuilder()->setTemplate('display');
        $this->loadModel('Packages');
        $packages = $this->Packages->find('package')
            ->where(['Packages.id IN' => $packageIds]);
        $this->set('packages', $packages);
    }
}
