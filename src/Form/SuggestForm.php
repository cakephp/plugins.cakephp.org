<?php
namespace App\Form;

use App\Form\BaseForm;
use Cake\Form\Schema;
use Cake\Validation\Validator;
use Josegonzalez\CakeQueuesadilla\Traits\QueueTrait;

class SuggestForm extends BaseForm
{
    use QueueTrait;

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
        preg_match('/([\w-]+\/[\w-]+)(?:\.git)?$/', $data['github'], $matches);
        $pieces = explode('/', $matches[1]);
        list($username, $repository) = $pieces;

        $ipaddress = null;
        if (isset($_SERVER['REMOTE_ADDR'], $ipaddress)) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        }

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
}
