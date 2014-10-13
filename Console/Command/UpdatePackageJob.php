<?php
App::uses('AppShell', 'Console/Command');

class UpdatePackageJob extends AppShell {

    public $uses = array('Package');

    public function work() {
    	$this->out(sprintf('CAKE_VERSION: %s', Configure::version()));
        $id = $this->args[0];
        $this->out(sprintf('Retrieving package %d', $id));
        $package = $this->Package->find('first', array(
            'conditions' => array('Package.id' => $id),
            'contain' => array('Maintainer'),
        ));

        if (empty($package)) {
            $this->out(sprintf('Package %d not found', $id));
            return;
        }

        if ($this->Package->updateAttributes($package)) {
            $this->out(sprintf('Package %d updated', $id));
        } else {
            $this->out(sprintf('Package %d not updated', $id));
        }
    }

}
