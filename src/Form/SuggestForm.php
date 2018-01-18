<?php
namespace App\Form;

use App\Form\BaseForm;
use Cake\Form\Schema;
use Cake\Validation\Validator;

class SuggestForm extends BaseForm
{
    protected function _buildSchema(Schema $schema)
    {
        return $schema->addField('github', 'string');
    }

    protected function _buildValidator(Validator $validator)
    {
        $validator->add('github', 'validUrl', [
            'message' => __('Invalid github repository url'),
            'rule' => function ($data, $provider) {
                if (!preg_match('/([\w-]+\/[\w-]+)(?:\.git)?$/', $data, $matches)) {
                    return false;
                }

                $pieces = explode('/', $matches[1]);
                return count($pieces) >= 2;
            }
        ]);

        return $validator->add('github', 'length', [
            'rule' => ['minLength', 3],
            'message' => 'Github url is required'
        ]);
    }

    protected function _execute(array $data)
    {
        $ipaddress = $this->getRequestIpAddress();

        preg_match('/([\w-]+\/[\w-]+)(?:\.git)?$/', $data['github'], $matches);
        $pieces = explode('/', $matches[1]);
        list($username, $repository) = $pieces;

        return $this->push(['\App\Job\SuggestPackageJob', 'perform'], [
            'ipaddress' => $ipaddress,
            'repository' => $repository,
            'username' => $username,
        ]);
    }

    public function route()
    {
        return ['controller' => 'Packages', 'action' => 'suggest'];
    }

    protected function getRequestIpAddress()
    {
        $ordered_choices = array(
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        );

        // check each server var in order
        // accepted ip must be non null and not private or reserved
        foreach ($ordered_choices as $var) {
            if (isset($_SERVER[$var])) {
                $ip = $_SERVER[$var];
                if ($ip && $this->isValidIp($ip)) {
                    return $ip;
                }
            }
        }

        return null;
    }

    protected function isValidIp($ip)
    {
        $options = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        return filter_var($ip, FILTER_VALIDATE_IP, $options) !== false;
    }
}
