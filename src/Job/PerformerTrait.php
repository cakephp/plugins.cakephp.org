<?php
namespace App\Job;

trait PerformerTrait
{
    protected function runJobInline($class, $method, $parameters)
    {
        $callable = [$class, $method];
        $performer = new Performer($callable, $parameters);

        return $performer->execute();
    }
}
