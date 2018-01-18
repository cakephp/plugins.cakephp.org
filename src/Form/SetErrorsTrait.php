<?php
namespace App\Form;

use Cake\Collection\Collection;
use Cake\Datasource\EntityInterface;

trait SetErrorsTrait
{
    public function setErrors(array $fields = null, $overwrite = false)
    {
        foreach ($fields as $f => $error) {
            $this->_errors += [$f => []];
            $this->_errors[$f] = $overwrite ?
                (array)$error :
                array_merge($this->_errors[$f], (array)$error);
        }

        return $this;
    }
}
