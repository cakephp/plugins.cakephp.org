<?php
namespace App\View\Cell;

use Cake\View\Cell;

class PackageListCell extends Cell
{
    public function featured()
    {
        $this->set('title', 'Featured Packages');
        $this->set(['disableAdmin' => true]);
        return $this->display([
            52, // debug_kit
            640, // asset_compress
            678, // cakephp-upload
            1600, // crud
            1911, // bootstrap-ui
            1402, // cakepdf
            1852, // footprint
            1764, // cakephp-jwt-auth
        ]);
    }

    public function popular()
    {
        $this->set('title', 'Popular Packages');
        $this->loadModel('Packages');
        $ids = $this->Packages->find('list')
            ->where(['Packages.deleted' => false])
            ->where(['Packages.tags LIKE' => '%version:3%'])
            ->orderDesc('Packages.watchers')
            ->limit(8)
            ->toArray();
        $this->set(['disableAdmin' => true]);
        return $this->display(array_keys($ids));
    }


    protected function display(array $packageIds = [])
    {
        $this->template = 'display';
        $this->loadModel('Packages');
        $packages = $this->Packages->find('package')
            ->where(['Packages.id IN' => $packageIds]);
        $this->set('packages', $packages);
    }
}
