<?php
App::uses('Shell', 'Console');

class AppShell extends Shell
{
    public function perform()
    {
        $this->initialize();
        $this->{array_shift($this->args)}();
    }
}
