<?php
if (!class_exists('DeferredEmail')) {
    App::import('Lib', 'CakeDjjob.DeferredEmail');
}
class BaseEmail extends DeferredEmail {
    
    public function build() {
        parent::build();
        if (!class_exists('Router')) {
            App::import('Core', 'Router');
        }

        if (!defined('FULL_BASE_URL')) {
            define('FULL_BASE_URL', Configure::read('Settings.FULL_BASE_URL'));
        }
        Configure::write('UrlCache.pageFiles', false);

        $this->updateVars(array(
            'delivery' => 'smtp',
            'smtpOptions' => array(
                'host' => 'ssl://smtp.gmail.com',
                'port' => '465',
                'timeout' => '30',
                'username' => Configure::read('Email.username'),
                'password' => Configure::read('Email.password')
            )
        ));
    }

}