<?php
namespace App\Form;

use App\Form\SetErrorsTrait;
use Cake\Form\Form;
use Cake\Log\Log;
use Josegonzalez\CakeQueuesadilla\Queue\Queue;
use Psr\Log\LogLevel;

class BaseForm extends Form
{
    use SetErrorsTrait;

    protected function push($callable, $args = [], $options = [])
    {
        Log::write(LogLevel::DEBUG, sprintf("Queuing %s", implode('::', (array)$callable)), $args);
        return Queue::push($callable, $args, $options);
    }
}
