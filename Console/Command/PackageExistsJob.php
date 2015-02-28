<?php
App::uses('AppShell', 'Console/Command');

class PackageExistsJob extends AppShell
{
    public $uses = array('Package');

    public function work()
    {
        $package = $this->args[0];
        $exists = $this->Package->findOnGithub($package);

        if ($exists) {
            $this->out(sprintf(__('* Record %s exists'), $package['Package']['id']));
            return;
        }

        if ($this->Package->softDelete($package['Package']['id'], false)) {
            $this->out(sprintf(__('* Record %s deleted'), $package['Package']['id']));
        } else {
            $this->out(sprintf(__('* Unable to delete record')));
        }
    }
}
