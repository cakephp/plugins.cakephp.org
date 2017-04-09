<?php
namespace App\Mailer;

use Cake\Core\Configure;
use Cake\Mailer\Mailer;

class PackageMailer extends Mailer
{
    use \Josegonzalez\MailPreview\Mailer\PreviewTrait;

    public function suggestPackage($username, $repository)
    {
        $this->viewVars([
            'repository' => $repository,
            'username' => $username,
        ]);
        $this
            ->to(Configure::read('Email.default.to'))
            ->subject(sprintf("New Package: %s/%s", $username, $repository));
    }
}
