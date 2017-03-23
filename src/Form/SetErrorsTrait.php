<?php
namespace App\Form;

trait SetErrorsTrait
{
    public function setErrors($field = null, $errors = null, $overwrite = false)
    {
        if ($field === null) {
            $diff = array_diff_key($this->_properties, $this->_errors);

            return $this->_errors + (new Collection($diff))
                ->filter(function ($value) {
                    return is_array($value) || $value instanceof EntityInterface;
                })
                ->map(function ($value) {
                    return $this->_readError($value);
                })
                ->filter()
                ->toArray();
        }

        if (is_string($field) && $errors === null) {
            $errors = isset($this->_errors[$field]) ? $this->_errors[$field] : [];
            if ($errors) {
                return $errors;
            }

            return $this->_nestedErrors($field);
        }

        if (!is_array($field)) {
            $field = [$field => $errors];
        }

        foreach ($field as $f => $error) {
            $this->_errors += [$f => []];
            $this->_errors[$f] = $overwrite ?
                (array)$error :
                array_merge($this->_errors[$f], (array)$error);
        }

        return $this;
    }
}
