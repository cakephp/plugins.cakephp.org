<?php
namespace App\Shell;

use App\Job\ClassifyJob;
use App\Job\CloneJob;
use App\Job\OrphanedPackageJob;
use App\Job\PerformerTrait;
use Cake\Console\Shell;

/**
 * FixOrphans shell command.
 */
class FixOrphansShell extends Shell
{
    use PerformerTrait;

    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        return $parser;
    }

    public function main()
    {
        $this->loadModel('Packages');

        $this->out('Retrieving orphaned packages');
        $packages = $this->Packages->find()
                                   ->leftJoin(['Maintainers' => 'maintainers'], 'Maintainers.id = Packages.maintainer_id')
                                   ->where(['Maintainers.id IS' => null])
                                   ->order(['Packages.repository_url' => 'asc']);
        foreach ($packages as $package) {
            $this->info(sprintf('%d %s/%s', $package->id, $package->maintainerName(), $package->name));
            $parameters = [
                'package_id' => $package->id,
            ];

            if (!$this->runJobInline(OrphanedPackageJob::class, 'perform', $parameters)) {
                $this->err(sprintf('%d %s/%s failed', $package->id, $package->maintainerName(), $package->name));
                continue;
            }

            if ($this->runJobInline(CloneJob::class, 'perform', $parameters)) {
                $this->runJobInline(ClassifyJob::class, 'perform', $parameters);
            }
        }
    }
}
