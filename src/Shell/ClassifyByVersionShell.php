<?php
namespace App\Shell;

use App\Job\ClassifyJob;
use App\Job\CloneJob;
use App\Job\OrphanedPackageJob;
use App\Job\PerformerTrait;
use Cake\Console\Shell;

/**
 * ClassifyByVersion shell command.
 */
class ClassifyByVersionShell extends Shell
{
    use PerformerTrait;

    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->addArgument('version', [
            'help' => 'CakePHP version to classify',
        ]);

        return $parser;
    }

    public function main()
    {
        $this->loadModel('Packages');

        $this->out(sprintf('Retrieving packages for cakephp version %d', $this->args[0]));
        $packages = $this->Packages->find('versioned', ['version' => $this->args[0]]);
        foreach ($packages as $package) {
            $this->info(sprintf('%d %s/%s', $package->id, $package->maintainerName(), $package->name));
            $parameters = [
                'package_id' => $package->id,
            ];

            if ($this->runJobInline(CloneJob::class, 'perform', $parameters)) {
                $this->runJobInline(ClassifyJob::class, 'perform', $parameters);
            }
        }
    }
}
