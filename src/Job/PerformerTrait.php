<?php
namespace App\Job;

use josegonzalez\Queuesadilla\Engine\NullEngine;
use josegonzalez\Queuesadilla\Job\Base;
use Psr\Log\NullLogger;

trait PerformerTrait
{
    protected function runJobInline($class, $method, $parameters)
    {
        $callable = [$class, $method];

        $item = [
            'id' => '1',
            'class' => $callable,
            'args' => [
                $parameters,
            ],
            'queue' => 'default',
            'options' => [
                'attempts_delay' => 600,
            ],
            'attempts' => 0,
        ];

        $job = new Base($item, new NullEngine(new NullLogger(), []));

        if (!is_callable($item['class'])) {
            return false;
        }

        $success = false;
        if (is_array($item['class']) && count($item['class']) == 2) {
            $className = $item['class'][0];
            $methodName = $item['class'][1];
            $instance = new $className();
            $success = $instance->$methodName($job);
        } elseif (is_string($item['class'])) {
            $success = call_user_func($item['class'], $job);
        }

        if ($success !== false) {
            $success = true;
        }

        return $success;
    }
}
