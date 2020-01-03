<?php
namespace App\Shell;

use Cake\Console\Shell;
use josegonzalez\Queuesadilla\Engine\NullEngine;
use josegonzalez\Queuesadilla\Job\Base;
use Psr\Log\NullLogger;

/**
 * TestClassifyJob shell command.
 */
class TestClassifyJobShell extends Shell
{

    /**
     * Manage the available sub-commands along with their arguments and help
     *
     * @see http://book.cakephp.org/3.0/en/console-and-shells.html#configuring-options-and-generating-help
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->addArgument('package_id', [
            'help' => 'ID of package to classify'
        ]);

        return $parser;
    }

    /**
     * main() method.
     *
     * @return bool|int|null Success or error code.
     */
    public function main()
    {
        if (empty($this->args[0])) {
            $this->err('Missing package_id argument');
            return false;
        }

        $item = [
            'id' => '30',
            'class' => [
                '\App\Job\ClassifyJob',
                'perform'
            ],
            'args' => [
                [
                    'package_id' => $this->args[0],
                ]
            ],
            'queue' => 'default',
            'options' => [
                'attempts_delay' => 600
            ],
            'attempts' => 0
        ];
        $this->perform($item);
    }

    public function perform($item)
    {
        $job = new Base($item, new NullEngine(new NullLogger, []));

        if (!is_callable($item['class'])) {
            return false;
        }

        $success = false;
        if (is_array($item['class']) && count($item['class']) == 2) {
            $className = $item['class'][0];
            $methodName = $item['class'][1];
            $instance = new $className;
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
