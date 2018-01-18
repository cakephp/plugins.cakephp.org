<?php
namespace App\Job;

use App\Job\DeferredEmail;
use Cake\Core\Configure;

class SuggestPackageJob extends DeferredEmail
{
    public function build()
    {
        parent::build();

        $data = $this->viewVars;
        $this->email->subject(sprintf("New Package: %s/%s", $data['username'], $data['repository']));
        $this->email->setTemplate('suggest_package');
        $this->email->to(Configure::read('Email.default.to'));
    }
}
