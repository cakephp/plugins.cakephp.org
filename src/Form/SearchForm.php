<?php
namespace App\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;

class SearchForm extends Form
{
    protected function _buildSchema(Schema $schema)
    {
        return $schema->addField('query', 'string');
    }

    protected function _buildValidator(Validator $validator)
    {
        return $validator->add('query', 'length', [
            'rule' => ['minLength', 3],
            'message' => 'Search query must be at least 3 characters'
        ]);
    }

    protected function _execute(array $data)
    {
        // Send an email.
        return true;
    }

    public function route()
    {
        return ['controller' => 'Packages', 'action' => 'index'];
    }
}
