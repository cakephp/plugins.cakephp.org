<?php
namespace App\Mailer\Preview;

use Josegonzalez\MailPreview\Mailer\Preview\MailPreview;

class PackageMailPreview extends MailPreview
{
    public function suggestPackage()
    {
        return $this->getMailer('Package')
                    ->emailFormat('both')
                    ->preview('suggestPackage', ['cakephp', 'cakephp']);
    }

    public function suggestPackageText()
    {
        return $this->getMailer('Package')
                    ->emailFormat('text')
                    ->preview('suggestPackage', ['cakephp', 'cakephp']);
    }
}
