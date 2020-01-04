<?php
namespace App\Job;

use App\Job\CreateMaintainerJob;
use App\Job\PerformerTrait;
use App\Traits\LogTrait;
use Cake\Datasource\ModelAwareTrait;
use Josegonzalez\CakeQueuesadilla\Traits\QueueTrait;
use josegonzalez\Queuesadilla\Job\Base;
use Psr\Log\NullLogger;

class OrphanedPackageJob
{
    use LogTrait;

    use ModelAwareTrait;

    use PerformerTrait;

    public function __construct()
    {
        $this->loadModel('Maintainers');
        $this->loadModel('Packages');
    }

    public function perform(Base $job)
    {
        $packageId = $job->data('package_id');
        $package = $this->Packages->findById($packageId)->first();
        if (empty($package)) {
            $this->error(sprintf('No package found in database for %d', $packageId));

            return false;
        }

        $maintainerName = $package->maintainerName();
        $maintainer = $this->Maintainers->findByUsername($maintainerName)->first();
        if (!empty($maintainer)) {
            $this->info(sprintf('Maintainer exists for %d, associating with %d', $packageId, $maintainer->id));
            $package->maintainer_id = $maintainer->id;

            return $this->Packages->save($package);
        }

        if (!$this->runJobInline(CreateMaintainerJob::class, 'perform', ['username' => $maintainerName])) {
            $this->error('Unable to create the maintainer');

            return true;
        }

        $maintainerName = $package->maintainerName();
        $maintainer = $this->Maintainers->findByUsername($maintainerName)->first();
        if (!empty($maintainer)) {
            $this->info(sprintf('Maintainer exists for %d, associating with %d', $packageId, $maintainer->id));
            $package->maintainer_id = $maintainer->id;

            return $this->Packages->save($package);
        }
    }
}
