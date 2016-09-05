<?php
namespace App\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;

class SuggestForm extends Form
{
    protected function _buildSchema(Schema $schema)
    {
        return $schema->addField('github_url', 'string');
    }

    protected function _buildValidator(Validator $validator)
    {
        return $validator->add('github_url', 'length', [
            'rule' => ['minLength', 3],
            'message' => 'Github url is required'
        ]);
    }

    protected function _execute(array $data)
    {
        // Send an email.
        return true;
    }

    public function route()
    {
        return ['controller' => 'Packages', 'action' => 'suggest'];
    }
}
