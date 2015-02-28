<?php
App::uses('AppShell', 'Console/Command');

class UpdatePackageJob extends AppShell
{
    public $uses = array('Package');

    public function work()
    {
        $package_id = $this->args[0];
        $this->out(sprintf('Retrieving package %d', $package_id));
        $package = $this->Package->find('first', array(
            'conditions' => array('Package.id' => $package_id),
            'contain' => array('Maintainer'),
        ));

        if (empty($package)) {
            $this->out(sprintf('Package %d not found', $package_id));
            return;
        }

        if ($this->Package->updateAttributes($package)) {
            $this->out(sprintf('Package %d updated', $package_id));
        } else {
            $this->out(sprintf('Package %d not updated', $package_id));
        }
    }
}
