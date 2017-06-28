<?php
namespace App\Shell;

use Cake\Datasource\ConnectionManager;
use Josegonzalez\CakeQueuesadilla\Shell\QueuesadillaShell as Shell;

class QueuesadillaShell extends Shell
{
    public function getWorker($engine, $logger)
    {
        $worker = parent::getWorker($engine, $logger);

        $worker->attachListener('Worker.job.start', function ($event) {
            $item = $event->data()['job']->item();
            if (is_array($item['class']) && count($item['class']) == 2) {
                $this->info(sprintf('Started job: %s', $className = $item['class'][0]));
            } else {
                $this->info('Started job: callable');
            }
        });

        $worker->attachListener('Worker.job.success', function ($event) {
            ConnectionManager::get('default')->disconnect();
        });
        $worker->attachListener('Worker.job.failure', function ($event) {
            ConnectionManager::get('default')->disconnect();
        });

        return $worker;
    }
}
